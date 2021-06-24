<?php

namespace portalium\user\controllers\backend\auth;

use Yii;
use yii\web\NotFoundHttpException;
use yii\rbac\Item;
use portalium\user\components\BaseAuthItemController;

/**
 * PermissionController implements the CRUD actions for AuthItem model.
 */
class PermissionController extends BaseAuthItemController
{

    /**
     * @inheritdoc
     */
    public function labels()
    {
        return [
            'Item' => 'Permission',
            'Items' => 'Permissions',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Item::TYPE_PERMISSION;
    }

    /**
     * {@inheritdoc}
     */
    public function actionCreate()
    {
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * {@inheritdoc}
     */
    public function actionDelete($id)
    {
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * {@inheritdoc}
     */
    public function actionUpdate($id)
    {
        throw new NotFoundHttpException('The requested page does not exist.');
    }


    /**
     * {@inheritdoc}
     */
    public function getViewPath()
    {
        return '@portalium/' . $this->module->id . '/views/' . Yii::$app->id . '/auth/item';
    }
}
