<?php

use portalium\db\Migration;
use portalium\user\rbac\OwnRule;

class m010101_010106_user_rbac_rule extends Migration
{
    public function up()
    {
        $auth = Yii::$app->authManager;
        $rule = new OwnRule();
        $auth->add($rule);
        $role = Yii::$app->setting->getValue('site::admin_role');
        $admin = (isset($role) && $role != '') ? $auth->getRole($role) : $auth->getRole('admin');

        $permissionsName = [
            'userApiDefaultViewOwn',
            'userApiDefaultUpdateOwn',
            'userApiDefaultDeleteOwn',
            'userWebDefaultCreateOwn',
            'userWebDefaultUpdateOwn',
            'userWebDefaultDeleteOwn',
            'userWebDefaultViewOwn',
            'userWebGroupViewOwn',
            'userWebGroupUpdateOwn',
            'userWebGroupMembersOwn',
            'userWebGroupDeleteOwn',
            
        ];
        foreach ($permissionsName as $permissionName) {
            $permission = $auth->createPermission($permissionName);
            $permission->description = $permissionName;
            $permission->ruleName = $rule->name;
            $auth->add($permission);
            $auth->addChild($admin, $permission);
            $childPermission = $auth->getPermission(str_replace('Own', '', $permissionName));
            $auth->addChild($permission, $childPermission);
        }

        $permissionsName = [
            'userApiDefaultIndexOwn',
            'userWebDefaultIndexOwn',
            'userWebGroupIndexOwn',
            'userWebImportIndexOwn',
        ];

        foreach ($permissionsName as $permissionName) {
            $permission = $auth->createPermission($permissionName);
            $permission->description = $permissionName;
            $auth->add($permission);
            $auth->addChild($admin, $permission);
        }
    }

    public function down()
    {
        $auth = Yii::$app->authManager;
        $auth->remove($auth->getPermission('userApiDefaultView'));
        $auth->remove($auth->getPermission('userApiDefaultCreate'));
        $auth->remove($auth->getPermission('userApiDefaultUpdate'));
        $auth->remove($auth->getPermission('userApiDefaultDelete'));
        $auth->remove($auth->getPermission('userApiDefaultIndex'));
        $auth->remove($auth->getPermission('userWebDefaultIndex'));
        $auth->remove($auth->getPermission('userWebDefaultCreate'));
        $auth->remove($auth->getPermission('userWebDefaultUpdate'));
        $auth->remove($auth->getPermission('userWebDefaultDelete'));
        $auth->remove($auth->getPermission('userWebDefaultView'));
        $auth->remove($auth->getPermission('userWebGroupIndex'));
        $auth->remove($auth->getPermission('userWebGroupView'));
        $auth->remove($auth->getPermission('userWebGroupCreate'));
        $auth->remove($auth->getPermission('userWebGroupUpdate'));
        $auth->remove($auth->getPermission('userWebGroupMembers'));
        $auth->remove($auth->getPermission('userWebGroupDelete'));
        $auth->remove($auth->getPermission('userWebImportIndex'));
        $auth->remove($auth->getPermission('userApiDefaultView'));
    }
}
