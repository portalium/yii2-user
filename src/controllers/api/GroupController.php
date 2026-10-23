<?php

namespace portalium\user\controllers\api;

use portalium\rest\ActiveController as RestActiveController;
use portalium\user\models\Group;
use portalium\user\models\GroupSearch;
use portalium\user\models\User;
use portalium\user\models\UserSearch;
use portalium\user\Module;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Groups and their members. Groups are global (no workspace), so every action needs a global permission.
 */
class GroupController extends RestActiveController
{
    public $modelClass = Group::class;

    private const PERMISSIONS = [
        'index' => 'userApiGroupIndex',
        'view' => 'userApiGroupView',
        'create' => 'userApiGroupCreate',
        'update' => 'userApiGroupUpdate',
        'delete' => 'userApiGroupDelete',
        'members' => 'userApiGroupMembers',
        'add-members' => 'userApiGroupMembers',
        'remove-members' => 'userApiGroupMembers',
    ];

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['delete']);
        $actions['index']['prepareDataProvider'] = function () {
            $q = Yii::$app->request->get('q');
            $q = is_string($q) ? trim($q) : '';
            return (new GroupSearch())->search(['GroupSearch' => ['name' => $q === '' ? null : $q]]);
        };
        return $actions;
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $permission = self::PERMISSIONS[$action->id] ?? null;
        if ($permission === null || !Yii::$app->user->can($permission)) {
            throw new ForbiddenHttpException(Module::t('You are not allowed to access this page.'));
        }

        return true;
    }

    /**
     * Users in the group (`in=1`, default) or not in it (`in=0`); `q` searches username, email and name.
     */
    public function actionMembers($id, $in = 1)
    {
        $search = new UserSearch();
        $search->setGroupId($this->findGroup($id)->id_group);
        (int) $in === 1 ? $search->inGroup() : $search->outGroup();
        $dataProvider = $search->search([]);

        $q = Yii::$app->request->get('q');
        $q = is_string($q) ? trim($q) : '';
        if ($q !== '') {
            $dataProvider->query->andWhere([
                'or',
                ['like', 'username', $q],
                ['like', 'email', $q],
                ['like', 'first_name', $q],
                ['like', 'last_name', $q],
            ]);
        }

        return $dataProvider;
    }

    /**
     * Body: `user_ids` (array). Users already in the group are skipped.
     */
    public function actionAddMembers($id)
    {
        $group = $this->findGroup($id);
        $members = $group->getUserGroups()->select('id_user')->column();
        $ids = array_diff($this->existingUserIds(), array_map('intval', $members));

        return $this->changeMembers($group, Group::SCENARIO_INSERT_USERS, $ids);
    }

    /**
     * Body: `user_ids` (array). Users not in the group are skipped.
     */
    public function actionRemoveMembers($id)
    {
        $group = $this->findGroup($id);
        $members = array_map('intval', $group->getUserGroups()->select('id_user')->column());
        $ids = array_intersect($this->existingUserIds(), $members);

        return $this->changeMembers($group, Group::SCENARIO_DELETE_USERS, $ids);
    }

    /**
     * Memberships are removed first (the foreign key would otherwise block the delete).
     */
    public function actionDelete($id)
    {
        $group = $this->findGroup($id);
        $members = $group->getUserGroups()->select('id_user')->column();

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($members !== []) {
                $group->scenario = Group::SCENARIO_DELETE_USERS;
                $group->setUserIds($members);
                if (!$group->save()) {
                    $transaction->rollBack();
                    return $this->modelError($group);
                }
            }
            if ($group->delete() === false) {
                $transaction->rollBack();
                return $this->error(['group' => Module::t('The group could not be deleted.')]);
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        Yii::$app->response->statusCode = 204;
        return null;
    }

    private function changeMembers(Group $group, string $scenario, array $ids): array
    {
        $group->scenario = $scenario;
        $group->setUserIds(array_values($ids));
        if (!$group->save()) {
            return $this->modelError($group);
        }

        return ['success' => true, 'changed' => count($ids)];
    }

    /**
     * @return int[] ids from the `user_ids` body parameter that belong to existing users
     */
    private function existingUserIds(): array
    {
        $ids = array_filter((array) Yii::$app->request->getBodyParam('user_ids', []), static function ($id) {
            return is_int($id) || (is_string($id) && ctype_digit($id));
        });
        if ($ids === []) {
            return [];
        }

        return array_map('intval', User::find()->select('id_user')->where(['id_user' => array_map('intval', $ids)])->column());
    }

    private function findGroup($id): Group
    {
        $group = Group::findOne((int) $id);
        if ($group === null) {
            throw new NotFoundHttpException(Module::t('The requested page does not exist.'));
        }

        return $group;
    }
}
