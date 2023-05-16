<?php

use portalium\db\Migration;

class m010101_010105_user_rbac extends Migration
{
    public function up()
    {
        $auth = Yii::$app->authManager;
        $role = Yii::$app->setting->getValue('site::admin_role');
        $admin = (isset($role) && $role != '') ? $auth->getRole($role) : $auth->getRole('admin');
        $auth->assign($admin, 1);

        $user = $auth->createRole('user');
        $user->description = 'User';
        $auth->add($user);
        $auth->addChild($admin, $user);

        $userApiDefaultView = $auth->createPermission('userApiDefaultView');
        $userApiDefaultView->description = 'User Api Default View';
        $auth->add($userApiDefaultView);
        $auth->addChild($admin, $userApiDefaultView);

        $userApiDefaultCreate = $auth->createPermission('userApiDefaultCreate');
        $userApiDefaultCreate->description = 'User Api Default Create';
        $auth->add($userApiDefaultCreate);
        $auth->addChild($admin, $userApiDefaultCreate);

        $userApiDefaultUpdate = $auth->createPermission('userApiDefaultUpdate');
        $userApiDefaultUpdate->description = 'User Api Default Update';
        $auth->add($userApiDefaultUpdate);
        $auth->addChild($admin, $userApiDefaultUpdate);

        $userApiDefaultDelete = $auth->createPermission('userApiDefaultDelete');
        $userApiDefaultDelete->description = 'User Api Default Delete';
        $auth->add($userApiDefaultDelete);
        $auth->addChild($admin, $userApiDefaultDelete);

        $userApiDefaultIndex = $auth->createPermission('userApiDefaultIndex');
        $userApiDefaultIndex->description = 'User Api Default Index';
        $auth->add($userApiDefaultIndex);
        $auth->addChild($admin, $userApiDefaultIndex);

        $userWebDefaultIndex = $auth->createPermission('userWebDefaultIndex');
        $userWebDefaultIndex->description = 'User Web Default Index';
        $auth->add($userWebDefaultIndex);
        $auth->addChild($admin, $userWebDefaultIndex);

        $userWebDefaultCreate = $auth->createPermission('userWebDefaultCreate');
        $userWebDefaultCreate->description = 'User Web Default Create';
        $auth->add($userWebDefaultCreate);
        $auth->addChild($admin, $userWebDefaultCreate);

        $userWebDefaultUpdate = $auth->createPermission('userWebDefaultUpdate');
        $userWebDefaultUpdate->description = 'User Web Default Update';
        $auth->add($userWebDefaultUpdate);
        $auth->addChild($admin, $userWebDefaultUpdate);

        $userWebDefaultDelete = $auth->createPermission('userWebDefaultDelete');
        $userWebDefaultDelete->description = 'User Web Default Delete';
        $auth->add($userWebDefaultDelete);
        $auth->addChild($admin, $userWebDefaultDelete);

        $userWebDefaultView = $auth->createPermission('userWebDefaultView');
        $userWebDefaultView->description = 'User Web Default View';
        $auth->add($userWebDefaultView);
        $auth->addChild($admin, $userWebDefaultView);

        $userWebGroupIndex = $auth->createPermission('userWebGroupIndex');
        $userWebGroupIndex->description = 'User Web Group Index';
        $auth->add($userWebGroupIndex);
        $auth->addChild($admin, $userWebGroupIndex);

        $userWebGroupView = $auth->createPermission('userWebGroupView');
        $userWebGroupView->description = 'User Web Group View';
        $auth->add($userWebGroupView);
        $auth->addChild($admin, $userWebGroupView);

        $userWebGroupCreate = $auth->createPermission('userWebGroupCreate');
        $userWebGroupCreate->description = 'User Web Group Create';
        $auth->add($userWebGroupCreate);
        $auth->addChild($admin, $userWebGroupCreate);

        $userWebGroupUpdate = $auth->createPermission('userWebGroupUpdate');
        $userWebGroupUpdate->description = 'User Web Group Update';
        $auth->add($userWebGroupUpdate);
        $auth->addChild($admin, $userWebGroupUpdate);

        $userWebGroupMembers = $auth->createPermission('userWebGroupMembers');
        $userWebGroupMembers->description = 'User Web Group Members members';
        $auth->add($userWebGroupMembers);
        $auth->addChild($admin, $userWebGroupMembers);

        $userWebGroupDelete = $auth->createPermission('userWebGroupDelete');
        $userWebGroupDelete->description = 'User Web Group Delete';
        $auth->add($userWebGroupDelete);
        $auth->addChild($admin, $userWebGroupDelete);

        $userWebImportIndex = $auth->createPermission('userWebImportIndex');
        $userWebImportIndex->description = 'User Web Import Index';
        $auth->add($userWebImportIndex);
        $auth->addChild($admin, $userWebImportIndex);
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
