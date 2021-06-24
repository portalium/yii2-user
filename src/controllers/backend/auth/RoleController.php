<?php

namespace portalium\user\controllers\backend\auth;

use Yii;
use yii\rbac\Item;
use portalium\user\components\BaseAuthItemController;

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
        return '@portalium/' . $this->module->id . '/views/' . Yii::$app->id . '/auth/item';
    }
}
