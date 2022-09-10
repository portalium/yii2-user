<?php

use yii\db\Migration;

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
        $this->createTable('user_group', [
            'id_group' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'description' => $this->text(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE NOW()'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('user_group');
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
