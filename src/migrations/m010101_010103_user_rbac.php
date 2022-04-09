<?php
use yii\db\Migration;

class m010101_010103_user_rbac extends Migration
{
    public function up()
    {
        $auth = Yii::$app->authManager;

        // add "createUser" permission
        $createUser = $auth->createPermission('createUser');
        $createUser->description = 'Create a User';
        $auth->add($createUser);

        // add "updateNews" permission
        $updateUser = $auth->createPermission('updateUser');
        $updateUser->description = 'Update User';
        $auth->add($updateUser);

        // add "deleteUser" permission
        $deleteUser = $auth->createPermission('deleteUser');
        $deleteUser->description = 'Delete User';
        $auth->add($deleteUser);

        // add "setRole" permission
        $setRole = $auth->createPermission('setRole');
        $setRole->description = 'Set a Role';
        $auth->add($setRole);

        // add "setAssignment" permission
        $setAssignment = $auth->createPermission('setAssignment');
        $setAssignment->description = 'Set Assignment';
        $auth->add($setAssignment);

        // add "createGroup" permission
        $createGroup = $auth->createPermission('createGroup');
        $createGroup->description = 'Create a Group';
        $auth->add($createGroup);

        // add "updateNews" permission
        $updateGroup = $auth->createPermission('updateGroup');
        $updateGroup->description = 'Update Group';
        $auth->add($updateGroup);

        // add "deleteGroup" permission
        $deleteGroup = $auth->createPermission('deleteGroup');
        $deleteGroup->description = 'Delete Group';
        $auth->add($deleteGroup);

        // add "membersUser" permission
        $membersGroup = $auth->createPermission('membersGroup');
        $membersGroup->description = 'Members User';
        $auth->add($membersGroup);

        // add "importUser" permission
        $importUser = $auth->createPermission('importUser');
        $importUser->description = 'Import User';
        $auth->add($importUser);

        // add "setPermission" permission
        $setPermission = $auth->createPermission('setPermission');
        $setPermission->description = 'Set Permission';
        $auth->add($setPermission);

        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $createUser);
        $auth->addChild($admin, $updateUser);
        $auth->addChild($admin, $deleteUser);
        $auth->addChild($admin, $setRole);
        $auth->addChild($admin, $setAssignment);
        $auth->addChild($admin, $createGroup);
        $auth->addChild($admin, $updateGroup);
        $auth->addChild($admin, $deleteGroup);
        $auth->addChild($admin, $membersGroup);
        $auth->addChild($admin, $importUser);
        $auth->addChild($admin, $setPermission);
        $auth->assign($admin, 1);

        $user = $auth->createRole('user');
        $auth->add($user);
    }

    public function down()
    {
        $auth = Yii::$app->authManager;
        $auth->remove($auth->getPermission("createUser"));
        $auth->remove($auth->getPermission("updateUser"));
        $auth->remove($auth->getPermission("deleteUser"));
        $auth->remove($auth->getPermission("setRole"));
        $auth->remove($auth->getPermission("setAssignment"));
        $auth->remove($auth->getPermission("createGroup"));
        $auth->remove($auth->getPermission("updateGroup"));
        $auth->remove($auth->getPermission("deleteGroup"));
        $auth->remove($auth->getPermission("membersGroup"));
        $auth->remove($auth->getPermission("importUser"));
        $auth->remove($auth->getPermission("setPermission"));
        $auth->remove($auth->getRole("admin"));

    }
}