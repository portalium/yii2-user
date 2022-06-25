<?php

namespace portalium\user\controllers\backend\auth;

use portalium\user\components\BulkAuthAssignmentHelper;
use portalium\user\Module;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use portalium\web\Controller as WebController;
use portalium\user\models\auth\Assignment;
use portalium\user\models\auth\AuthItem;
use portalium\user\models\GroupSearch;
use portalium\user\models\UserSearch;

/**
 * Bulk Assignment Controller
 */
class BulkAssignmentController extends WebController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'assign' => ['post'],
                    'revoke' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @return mixed
     */
    public function actionIndex($id)
    {
        if (!Yii::$app->user->can('userBackendBulkAssignmentIndex'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to set Assignment"));

        $model = $this->findModel($id);
        return $this->render('index', [
            'groupDataProvider' => (new GroupSearch())->search($this->request->queryParams),
            'userDataProvider' => (new UserSearch())->search($this->request->queryParams),
            'assignedUsers' => BulkAuthAssignmentHelper::getAssignedUsers($id)->select(['id_user', 'username'])->all(),
            'model' => $model,
        ]);
    }

    /**
     * @param string $id
     * @return array
     */
    public function actionAssign($id)
    {
        if (!Yii::$app->user->can('userBackendBulkAssignmentAssign'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to set Assignment"));
        $success = BulkAuthAssignmentHelper::assignByMixed($id, $this->request->post('items', []));
        Yii::$app->getResponse()->format = 'json';
        return array_merge(['assignedUsers' => BulkAuthAssignmentHelper::getAssignedUsers($id)->select(['id_user', 'username'])->all()], ['success' => $success]);
    }

    /**
     * @param string $id
     * @return array
     */
    public function actionRevoke($id)
    {
        if (!Yii::$app->user->can('userBackendBulkAssignmentRevoke'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to set Assignment"));
        $success = BulkAuthAssignmentHelper::revokeByMixed($id, $this->request->post('items', []));
        Yii::$app->getResponse()->format = 'json';
        return array_merge(['assignedUsers' => BulkAuthAssignmentHelper::getAssignedUsers($id)->select(['id_user', 'username'])->all()], ['success' => $success]);
    }

    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param  integer $id
     * @return Assignment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AuthItem::find($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
