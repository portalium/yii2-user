<?php

use yii\db\Migration;
use portalium\user\Module as UserModule;

class m261021_000001_add_sms_verified_to_user extends Migration
{
    public function safeUp()
    {
        $table = '{{%' . UserModule::$tablePrefix . 'user}}';

        $this->addColumn($table, 'tel_no', $this->string(16)->null());
        $this->addColumn($table, 'sms_verified', $this->smallInteger()->notNull()->defaultValue(20));
        $this->createIndex('uq-user_user-tel_no', $table, 'tel_no', true);
    }


    public function safeDown()
    {
        $table = '{{%' . UserModule::$tablePrefix . 'user}}';

        $this->dropIndex('uq-user_user-tel_no', $table);
        $this->dropColumn($table, 'sms_verified');
        $this->dropColumn($table, 'tel_no');
    }
}
