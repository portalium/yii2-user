<?php
use yii\db\Migration;

class m010101_010105_user_rbac extends Migration
{
    public function up()
    {
        $auth = Yii::$app->authManager;
        
        $settings = yii\helpers\ArrayHelper::map(portalium\site\models\Setting::find()->asArray()->all(),'name','value');
        $role = $settings['default::role'];
        $admin = (isset($role) && $role != '') ? $auth->getRole($role) : $auth->getRole('admin');

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
        $auth->remove($auth->getPermission("viewUser"));
        $auth->remove($auth->getPermission("viewGroup"));
        
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

        $userBackendAssignmentView = $auth->createPermission('userBackendAssignmentView');
        $userBackendAssignmentView->description = 'View user assignment';
        $auth->add($userBackendAssignmentView);
        $auth->addChild($admin, $userBackendAssignmentView);

        $userBackendAssignmentAssign = $auth->createPermission('userBackendAssignmentAssign');
        $userBackendAssignmentAssign->description = 'Assign user assignment';
        $auth->add($userBackendAssignmentAssign);
        $auth->addChild($admin, $userBackendAssignmentAssign);

        $userBackendAssignmentRevoke = $auth->createPermission('userBackendAssignmentRevoke');
        $userBackendAssignmentRevoke->description = 'Revoke user assignment';
        $auth->add($userBackendAssignmentRevoke);
        $auth->addChild($admin, $userBackendAssignmentRevoke);

        $userBackendBulkAssignmentIndex = $auth->createPermission('userBackendBulkAssignmentIndex');
        $userBackendBulkAssignmentIndex->description = 'View bulk assignment';
        $auth->add($userBackendBulkAssignmentIndex);
        $auth->addChild($admin, $userBackendBulkAssignmentIndex);

        $userBackendBulkAssignmentAssign = $auth->createPermission('userBackendBulkAssignmentAssign');
        $userBackendBulkAssignmentAssign->description = 'Assign bulk assignment';
        $auth->add($userBackendBulkAssignmentAssign);
        $auth->addChild($admin, $userBackendBulkAssignmentAssign);

        $userBackendBulkAssignmentRevoke = $auth->createPermission('userBackendBulkAssignmentRevoke');
        $userBackendBulkAssignmentRevoke->description = 'Revoke bulk assignment';
        $auth->add($userBackendBulkAssignmentRevoke);
        $auth->addChild($admin, $userBackendBulkAssignmentRevoke);

        $userBackendPermissionViewPath = $auth->createPermission('userBackendPermissionViewPath');
        $userBackendPermissionViewPath->description = 'View permission path';
        $auth->add($userBackendPermissionViewPath);
        $auth->addChild($admin, $userBackendPermissionViewPath);

        $userBackendDefaultIndex = $auth->createPermission('userBackendDefaultIndex');
        $userBackendDefaultIndex->description = 'View user';
        $auth->add($userBackendDefaultIndex);
        $auth->addChild($admin, $userBackendDefaultIndex);

        $userBackendDefaultCreate = $auth->createPermission('userBackendDefaultCreate');
        $userBackendDefaultCreate->description = 'Create user';
        $auth->add($userBackendDefaultCreate);
        $auth->addChild($admin, $userBackendDefaultCreate);

        $userBackendDefaultUpdate = $auth->createPermission('userBackendDefaultUpdate');
        $userBackendDefaultUpdate->description = 'Update user';
        $auth->add($userBackendDefaultUpdate);
        $auth->addChild($admin, $userBackendDefaultUpdate);

        $userBackendDefaultDelete = $auth->createPermission('userBackendDefaultDelete');
        $userBackendDefaultDelete->description = 'Delete user';
        $auth->add($userBackendDefaultDelete);
        $auth->addChild($admin, $userBackendDefaultDelete);

        $userBackendDefaultView = $auth->createPermission('userBackendDefaultView');
        $userBackendDefaultView->description = 'View user';
        $auth->add($userBackendDefaultView);
        $auth->addChild($admin, $userBackendDefaultView);

        $userBackendGroupIndex = $auth->createPermission('userBackendGroupIndex');
        $userBackendGroupIndex->description = 'View group';
        $auth->add($userBackendGroupIndex);
        $auth->addChild($admin, $userBackendGroupIndex);

        $userBackendGroupView = $auth->createPermission('userBackendGroupView');
        $userBackendGroupView->description = 'View group';
        $auth->add($userBackendGroupView);
        $auth->addChild($admin, $userBackendGroupView);

        $userBackendGroupCreate = $auth->createPermission('userBackendGroupCreate');
        $userBackendGroupCreate->description = 'Create group';
        $auth->add($userBackendGroupCreate);
        $auth->addChild($admin, $userBackendGroupCreate);

        $userBackendGroupUpdate = $auth->createPermission('userBackendGroupUpdate');
        $userBackendGroupUpdate->description = 'Update group';
        $auth->add($userBackendGroupUpdate);
        $auth->addChild($admin, $userBackendGroupUpdate);

        $userBackendGroupMembers = $auth->createPermission('userBackendGroupMembers');
        $userBackendGroupMembers->description = 'View group members';
        $auth->add($userBackendGroupMembers);
        $auth->addChild($admin, $userBackendGroupMembers);

        $userBackendGroupDelete = $auth->createPermission('userBackendGroupDelete');
        $userBackendGroupDelete->description = 'Delete group';
        $auth->add($userBackendGroupDelete);
        $auth->addChild($admin, $userBackendGroupDelete);

        $userBackendImportIndex = $auth->createPermission('userBackendImportIndex');
        $userBackendImportIndex->description = 'View import';
        $auth->add($userBackendImportIndex);
        $auth->addChild($admin, $userBackendImportIndex);

        //userBackendRoleViewPath
        $userBackendRoleViewPath = $auth->createPermission('userBackendRoleViewPath');
        $userBackendRoleViewPath->description = 'View role path';
        $auth->add($userBackendRoleViewPath);
        $auth->addChild($admin, $userBackendRoleViewPath);
    }

    public function down()
    {
        $auth = Yii::$app->authManager;

        $auth->remove($auth->getPermission('userApiDefaultIndex'));
        $auth->remove($auth->getPermission('userApiDefaultCreate'));
        $auth->remove($auth->getPermission('userApiDefaultUpdate'));
        $auth->remove($auth->getPermission('userApiDefaultDelete'));
        $auth->remove($auth->getPermission('userApiDefaultView'));
        $auth->remove($auth->getPermission('userApiDefaultAssignmentView'));
        $auth->remove($auth->getPermission('userApiDefaultAssignmentAssign'));
        $auth->remove($auth->getPermission('userApiDefaultAssignmentRevoke'));
        $auth->remove($auth->getPermission('userApiBulkAssignmentIndex'));
        $auth->remove($auth->getPermission('userApiBulkAssignmentAssign'));
        $auth->remove($auth->getPermission('userApiBulkAssignmentRevoke'));
        $auth->remove($auth->getPermission('userApiPermissionViewPath'));
        $auth->remove($auth->getPermission('userBackendDefaultIndex'));
        $auth->remove($auth->getPermission('userBackendDefaultCreate'));
        $auth->remove($auth->getPermission('userBackendDefaultUpdate'));
        $auth->remove($auth->getPermission('userBackendDefaultDelete'));
        $auth->remove($auth->getPermission('userBackendDefaultView'));
        $auth->remove($auth->getPermission('userBackendGroupIndex'));
        $auth->remove($auth->getPermission('userBackendGroupView'));
        $auth->remove($auth->getPermission('userBackendGroupCreate'));
        $auth->remove($auth->getPermission('userBackendGroupUpdate'));
        $auth->remove($auth->getPermission('userBackendGroupMembers'));
        $auth->remove($auth->getPermission('userBackendGroupDelete'));
        $auth->remove($auth->getPermission('userBackendImportIndex'));

    }
}