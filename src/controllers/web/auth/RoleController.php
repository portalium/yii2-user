<?php

namespace portalium\user\controllers\web\auth;

use portalium\user\Module;
use Yii;
use yii\rbac\Item;
use portalium\user\components\BaseAuthItemController;
use yii\web\ForbiddenHttpException;

/**
 * RoleController implements the CRUD actions for AuthItem model.
 *
 */
class RoleController extends BaseAuthItemController
{
    /**
     * @inheritdoc
     */
    public function labels()
    {
        return[
            'Item' => 'Role',
            'Items' => 'Roles',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Item::TYPE_ROLE;
    }


    public function getViewPath()
    {
        if (!Yii::$app->user->can('userBackendRoleViewPath'))
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        return '@portalium/' . $this->module->id . '/views/' . Yii::$app->id . '/auth/item';
    }
}
