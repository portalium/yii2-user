<?php

use portalium\db\Migration;
use portalium\user\Module;

class m010101_010101_user_user extends Migration
{
    public function up()
    {

        $this->createTable(Module::$tablePrefix . 'user', [
            'id_user' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'first_name' => $this->string(),
            'last_name' => $this->string(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'email' => $this->string()->notNull()->unique(),
            'access_token' => $this->string()->notNull()->unique(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE NOW()'),
        ]);
    }

    public function down()
    {
        $this->dropTable(Module::$tablePrefix . 'user');
    }
}