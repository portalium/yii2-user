<?php

use yii\db\Migration;

class m010101_010105_user_rbac extends Migration
{
    public function up()
    {
        $auth = Yii::$app->authManager;
        $admin = $auth->createRole('admin');
        $admin->description = 'Admin';
        $auth->add($admin);

        $user = $auth->createRole('user');
        $user->description = 'User';
        $auth->add($user);

        $userApiDefaultView = $auth->createPermission('userApiDefaultView');
        $userApiDefaultView->description = 'View user';
        $auth->add($userApiDefaultView);
        $auth->addChild($admin, $userApiDefaultView);

        $userApiDefaultCreate = $auth->createPermission('userApiDefaultCreate');
        $userApiDefaultCreate->description = 'Create user';
        $auth->add($userApiDefaultCreate);
        $auth->addChild($admin, $userApiDefaultCreate);

        $userApiDefaultUpdate = $auth->createPermission('userApiDefaultUpdate');
        $userApiDefaultUpdate->description = 'Update user';
        $auth->add($userApiDefaultUpdate);
        $auth->addChild($admin, $userApiDefaultUpdate);

        $userApiDefaultDelete = $auth->createPermission('userApiDefaultDelete');
        $userApiDefaultDelete->description = 'Delete user';
        $auth->add($userApiDefaultDelete);
        $auth->addChild($admin, $userApiDefaultDelete);

        $userApiDefaultIndex = $auth->createPermission('userApiDefaultIndex');
        $userApiDefaultIndex->description = 'View user';
        $auth->add($userApiDefaultIndex);
        $auth->addChild($admin, $userApiDefaultIndex);

        $userWebDefaultIndex = $auth->createPermission('userWebDefaultIndex');
        $userWebDefaultIndex->description = 'View user';
        $auth->add($userWebDefaultIndex);
        $auth->addChild($admin, $userWebDefaultIndex);

        $userWebDefaultCreate = $auth->createPermission('userWebDefaultCreate');
        $userWebDefaultCreate->description = 'Create user';
        $auth->add($userWebDefaultCreate);
        $auth->addChild($admin, $userWebDefaultCreate);

        $userWebDefaultUpdate = $auth->createPermission('userWebDefaultUpdate');
        $userWebDefaultUpdate->description = 'Update user';
        $auth->add($userWebDefaultUpdate);
        $auth->addChild($admin, $userWebDefaultUpdate);

        $userWebDefaultDelete = $auth->createPermission('userWebDefaultDelete');
        $userWebDefaultDelete->description = 'Delete user';
        $auth->add($userWebDefaultDelete);
        $auth->addChild($admin, $userWebDefaultDelete);

        $userWebDefaultView = $auth->createPermission('userWebDefaultView');
        $userWebDefaultView->description = 'View user';
        $auth->add($userWebDefaultView);
        $auth->addChild($admin, $userWebDefaultView);

        $userWebGroupIndex = $auth->createPermission('userWebGroupIndex');
        $userWebGroupIndex->description = 'View group';
        $auth->add($userWebGroupIndex);
        $auth->addChild($admin, $userWebGroupIndex);

        $userWebGroupView = $auth->createPermission('userWebGroupView');
        $userWebGroupView->description = 'View group';
        $auth->add($userWebGroupView);
        $auth->addChild($admin, $userWebGroupView);

        $userWebGroupCreate = $auth->createPermission('userWebGroupCreate');
        $userWebGroupCreate->description = 'Create group';
        $auth->add($userWebGroupCreate);
        $auth->addChild($admin, $userWebGroupCreate);

        $userWebGroupUpdate = $auth->createPermission('userWebGroupUpdate');
        $userWebGroupUpdate->description = 'Update group';
        $auth->add($userWebGroupUpdate);
        $auth->addChild($admin, $userWebGroupUpdate);

        $userWebGroupMembers = $auth->createPermission('userWebGroupMembers');
        $userWebGroupMembers->description = 'View group members';
        $auth->add($userWebGroupMembers);
        $auth->addChild($admin, $userWebGroupMembers);

        $userWebGroupDelete = $auth->createPermission('userWebGroupDelete');
        $userWebGroupDelete->description = 'Delete group';
        $auth->add($userWebGroupDelete);
        $auth->addChild($admin, $userWebGroupDelete);

        $userWebImportIndex = $auth->createPermission('userWebImportIndex');
        $userWebImportIndex->description = 'View import';
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
