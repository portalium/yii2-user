<?php

namespace portalium\user\controllers\web\auth;

use portalium\user\Module;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use portalium\web\Controller as WebController;
use portalium\user\models\auth\Assignment;
use portalium\user\models\User;

/**
 * AssignmentController implements the CRUD actions for Assignment model.
 */
class AssignmentController extends WebController
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
     * Displays a single Assignment model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        if (!Yii::$app->user->can('userWebAssignmentView'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to set Assignment"));

        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Assign items
     * @param string $id
     * @return array
     */
    public function actionAssign($id)
    {
        if (!Yii::$app->user->can('userWebAssignmentAssign'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to set Assignment"));

        $items = $this->request->post('items', []);
        $model = new Assignment($id);
        $success = $model->assign($items);
        Yii::$app->getResponse()->format = 'json';
        return array_merge($model->getItems(), ['success' => $success]);
    }

    /**
     * Assign items
     * @param string $id
     * @return array
     */
    public function actionRevoke($id)
    {
        if (!Yii::$app->user->can('userWebAssignmentRevoke'))
            throw new ForbiddenHttpException(Module::t("Sorry you are not allowed to set Assignment"));

        $items = $this->request->post('items', []);
        $model = new Assignment($id);
        $success = $model->revoke($items);
        Yii::$app->getResponse()->format = 'json';
        return array_merge($model->getItems(), ['success' => $success]);
    }

    /**
     * Finds the Assignment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param  integer $id
     * @return Assignment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($user = User::findIdentity($id)) !== null) {
            return new Assignment($id, $user);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
