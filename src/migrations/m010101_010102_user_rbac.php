<?php
use yii\db\Migration;

class m010101_010102_user_rbac extends Migration
{
    public function up()
    {
        $auth = Yii::$app->authManager;

        // add "viewUser" permission
        $viewUser = $auth->createPermission('viewUser');
        $viewUser->description = 'View a User';
        $auth->add($viewUser);

        // add "viewGroup" permission
        $viewGroup = $auth->createPermission('viewGroup');
        $viewGroup->description = 'View a Group';
        $auth->add($viewGroup);

        $admin = $auth->getRole('admin');

        $auth->addChild($admin, $viewUser);
        $auth->addChild($admin, $viewGroup);
    }

    public function down()
    {
        $auth = Yii::$app->authManager;
        $auth->remove($auth->getPermission("viewUser"));
        $auth->remove($auth->getPermission("viewGroup"));

    }
}