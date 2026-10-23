<?php

namespace portalium\user\controllers\api;

use Yii;
use yii\validators\EmailValidator;
use portalium\rest\ActiveController as RestActiveController;
use portalium\site\Module;
use portalium\user\Module as UserModule;
use portalium\user\models\UserForm;
use portalium\user\models\UserSearch;
use portalium\user\models\User;
use portalium\user\components\UserDeleteException;
use portalium\user\components\UserDeleter;

class UsersController extends RestActiveController
{
    public $modelClass = 'portalium\user\models\User';

    /**
     * List filters; anything else (access_token, password_hash, ...) is ignored so the list can't be used
     * to guess another account's secrets with LIKE.
     */
    private const FILTERS = ['username', 'first_name', 'last_name', 'email', 'status', 'is_virtual'];

    /** Attributes an administrator may change; a user editing their own record gets only the first four. */
    private const ADMIN_FIELDS = ['first_name', 'last_name', 'username', 'email', 'status', 'is_virtual'];
    private const OWN_FIELDS = ['first_name', 'last_name', 'username', 'email'];

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete']);
        $actions['index']['prepareDataProvider'] = function ($action) {
            $params = Yii::$app->request->queryParams;
            $filters = array_intersect_key(array_merge((array) ($params['UserSearch'] ?? []), $params), array_flip(self::FILTERS));
            // is_virtual: omitted means real users only, 'all' means both, 0/1 means that type only.
            $isVirtual = $filters['is_virtual'] ?? null;
            if ($isVirtual === 'all') {
                unset($filters['is_virtual']);
            }
            $searchModel = new UserSearch();
            $dataProvider = $searchModel->search(['UserSearch' => $filters]);
            if (!Yii::$app->user->can('userApiDefaultIndex')) {
                $dataProvider->query->andWhere(['id_user' => Yii::$app->user->id]);
            }
            if ($isVirtual === null) {
                $dataProvider->query->andWhere(['is_virtual' => User::IS_VIRTUAL_FALSE]);
            }
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
        };
        return $actions;
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $id = Yii::$app->request->get('id');
        $model = null;

        if (in_array($action->id, ['view', 'update', 'delete', 'delete-options']) && $id) {
            $model = User::findOne($id);
            if (!$model) {
                throw new \yii\web\NotFoundHttpException(Module::t('User not found.'));
            }
        }

        switch ($action->id) {
            case 'view':
                if (!Yii::$app->user->can('userApiDefaultView') && !Yii::$app->user->can('userApiDefaultViewOwn', ['model' => $model])) {
                    throw new \yii\web\ForbiddenHttpException(Module::t('You do not have permission to view this menu.'));
                }
                break;
            case 'create':
                if (!Yii::$app->user->can('userApiDefaultCreate')) {
                    throw new \yii\web\ForbiddenHttpException(Module::t('You do not have permission to create this menu.'));
                }
                break;
            case 'update':
                if (!Yii::$app->user->can('userApiDefaultUpdate') && !Yii::$app->user->can('userApiDefaultUpdateOwn', ['model' => $model])) {
                    throw new \yii\web\ForbiddenHttpException(Module::t('You do not have permission to update this menu.'));
                }
                break;
            case 'delete-options':
                // Returns every username as a transfer target; only the global delete permission may see it.
                if (!Yii::$app->user->can('userApiDefaultDelete')) {
                    throw new \yii\web\ForbiddenHttpException(Module::t('You do not have permission to delete this menu.'));
                }
                break;
            case 'delete':
                if (!Yii::$app->user->can('userApiDefaultDelete') && !Yii::$app->user->can('userApiDefaultDeleteOwn', ['model' => $model])) {
                    throw new \yii\web\ForbiddenHttpException(Module::t('You do not have permission to delete this menu.'));
                }
                break;
            case 'search-username':
                break;//TODO: Dont think this needs a special permission but leaving it like this dosent feel right either
            default:
                if (!Yii::$app->user->can('userApiDefaultIndex') && !Yii::$app->user->can('userApiDefaultIndexOwn')) {
                    throw new \yii\web\ForbiddenHttpException(Module::t('You do not have permission to delete this menu.'));
                }
                break;
        }

        return true;
    }

    /**
     * Creates a user the way the web form does (`is_virtual` = 1 for a virtual user) and returns the record.
     * The new account's access token is not returned.
     */
    public function actionCreate()
    {
        $form = new UserForm();
        $form->load(Yii::$app->getRequest()->getBodyParams(), '');
        $user = $form->createUser();
        if ($user === null) {
            return $form->hasErrors()
                ? $this->modelError($form)
                : $this->error(['user' => UserModule::t('The user could not be saved.')]);
        }

        Yii::$app->response->setStatusCode(201);
        return $user;
    }

    public function actionUpdate($id)
    {
        $user = User::findOne($id);
        $fields = Yii::$app->user->can('userApiDefaultUpdate') ? self::ADMIN_FIELDS : self::OWN_FIELDS;
        $values = array_intersect_key(Yii::$app->getRequest()->getBodyParams(), array_flip($fields));
        $user->setAttributes(array_map(static fn ($value) => is_string($value) ? trim($value) : $value, $values));

        if (array_key_exists('username', $values) && (string) $user->username === '') {
            $user->addError('username', UserModule::t('Username cannot be blank.'));
        }
        if (array_key_exists('email', $values) && !(new EmailValidator())->validate($user->email)) {
            $user->addError('email', UserModule::t('Email is not a valid email address.'));
        }
        foreach (['username' => 'This username has already been taken.', 'email' => 'This email address has already been taken.'] as $attribute => $message) {
            $taken = $user->isAttributeChanged($attribute) && User::find()
                ->where([$attribute => $user->$attribute])
                ->andWhere(['<>', 'id_user', $user->id_user])
                ->exists();
            if ($taken) {
                $user->addError($attribute, UserModule::t($message));
            }
        }

        $this->assertCanBeClosed($user);

        if ($user->hasErrors() || !$user->save()) {
            return $this->modelError($user);
        }

        return $user;
    }

    /**
     * Marking the user passive or deleted drops their session (the token is rotated): the same
     * rule as deletion — a user can't close their own account or the last administrator's.
     */
    private function assertCanBeClosed(User $user): void
    {
        $closing = (int) $user->status !== User::STATUS_ACTIVE && (int) $user->getOldAttribute('status') === User::STATUS_ACTIVE;
        if (!$closing) {
            return;
        }
        if ((int) $user->id_user === (int) Yii::$app->user->id) {
            $user->addError('status', UserModule::t('You cannot deactivate your own account.'));
            return;
        }

        $auth = Yii::$app->authManager;
        $role = Yii::$app->setting->getValue('site::admin_role') ?: 'admin';
        if ($auth->getAssignment($role, $user->id_user) === null) {
            return;
        }
        $others = array_diff(array_map('intval', $auth->getUserIdsByRole($role)), [(int) $user->id_user]);
        $activeOthers = $others === [] ? 0 : (int) User::find()->where(['id_user' => $others, 'status' => User::STATUS_ACTIVE])->count();
        if ($activeOthers === 0) {
            $user->addError('status', UserModule::t('The last administrator cannot be deactivated.'));
        }
    }

    /**
     * What the delete dialog needs: modules whose data can be deleted instead of transferred, and the
     * active real users the remaining data can be transferred to.
     */
    public function actionDeleteOptions($id)
    {
        return [
            'modules' => array_keys(UserDeleter::handlerModules()),
            'users' => User::find()
                ->select(['id_user', 'username'])
                ->where(['status' => User::STATUS_ACTIVE, 'is_virtual' => User::IS_VIRTUAL_FALSE])
                ->andWhere(['<>', 'id_user', (int) $id])
                ->orderBy('username')
                ->asArray()
                ->all(),
        ];
    }


    /**
     * Search users by partial username.
     * 
     * @param string $username
     * @return ActiveDataProvider
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSearchUsername($username)
    {
        if (empty($username)) {
            throw new \yii\web\BadRequestHttpException(Module::t('Username parameter is required.'));
        }

        $query = User::find()
        ->select(['username', 'id_avatar'])
        ->where(['like', 'username', $username])
        ->andWhere(['status' => User::STATUS_ACTIVE])
        ->asArray();

        $dataprovider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'username',
            'pagination' => [
                'defaultPageSize' => 10,
                'validatePage' => false,
            ],
        ]);

        return $dataprovider;
    }

    /**
     * Deletes the user through the same service the web UI uses.
     * Body or query: `modules` (modules to delete), `default_user` (user to transfer remaining data to).
     */
    public function actionDelete($id)
    {
        $request = Yii::$app->getRequest();
        $body = array_merge($request->getQueryParams(), $request->getBodyParams());
        $modules = array_map('strval', (array) ($body['modules'] ?? []));
        $defaultUser = empty($body['default_user']) ? null : (int) $body['default_user'];

        try {
            UserDeleter::delete((int) $id, $modules, $defaultUser);
        } catch (UserDeleteException $e) {
            Yii::$app->response->statusCode = 422;
            return ['message' => $e->getMessage()];
        }

        Yii::$app->response->statusCode = 204;
        return null;
    }
}
