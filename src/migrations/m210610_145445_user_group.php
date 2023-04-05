<?php

use portalium\db\Migration;
use portalium\user\Module;

/**
 * Class m210610_145445_group
 */
class m210610_145445_user_group extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(Module::$tablePrefix . 'group', [
            'id_group' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'description' => $this->text(),
            'date_create' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'date_update' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE NOW()'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(Module::$tablePrefix . 'group');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210610_145445_group cannot be reverted.\n";

        return false;
    }
    */
}
