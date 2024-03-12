<?php

use portalium\db\Migration;
use portalium\user\Module;

class m010101_010101_user_user extends Migration
{
    public function up()
    {

        $this->createTable(Module::$tablePrefix . 'user', [
            'id_user' => $this->primaryKey(),
            'id_avatar'=>$this->integer(),
            'username' => $this->string()->notNull()->unique(),
            'first_name' => $this->string(),
            'last_name' => $this->string(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'email' => $this->string()->notNull()->unique(),
            'access_token' => $this->string()->notNull()->unique(),
            'email_verify'=>$this->smallInteger()->notNull()->defaultValue(20),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'verification_token' => $this->string()->defaultValue(null),
            'date_create' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'date_update' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE NOW()'),
        ]);
    }

    public function down()
    {
        $this->dropTable(Module::$tablePrefix . 'user');
    }
}