<?php

use portalium\db\Migration;

class m261023_000010_user_api_group_import_rbac extends Migration
{
    private const PERMISSIONS = [
        'userApiGroupIndex',
        'userApiGroupView',
        'userApiGroupCreate',
        'userApiGroupUpdate',
        'userApiGroupDelete',
        'userApiGroupMembers',
        'userApiImportIndex',
    ];

    public function up()
    {
        $auth = Yii::$app->authManager;
        $role = Yii::$app->setting->getValue('site::admin_role');
        $admin = (isset($role) && $role != '') ? $auth->getRole($role) : $auth->getRole('admin');
        foreach (self::PERMISSIONS as $permissionName) {
            if ($auth->getPermission($permissionName) !== null) {
                continue;
            }
            $permission = $auth->createPermission($permissionName);
            $permission->description = ucfirst(str_replace('user', '', $permissionName));
            $auth->add($permission);
            $auth->addChild($admin, $permission);
        }
    }

    public function down()
    {
        $auth = Yii::$app->authManager;
        foreach (self::PERMISSIONS as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            if ($permission !== null) {
                $auth->remove($permission);
            }
        }
    }
}
