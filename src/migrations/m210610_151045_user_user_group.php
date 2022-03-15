<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_group}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%group}}`
 */
class m210610_151045_user_user_group extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user_user_group}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'group_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-user_group-user_id}}',
            '{{%user_user_group}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-user_group-user_id}}',
            '{{%user_user_group}}',
            'user_id',
            '{{%user_user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `group_id`
        $this->createIndex(
            '{{%idx-user_group-group_id}}',
            '{{%user_user_group}}',
            'group_id'
        );

        // add foreign key for table `{{%group}}`
        $this->addForeignKey(
            '{{%fk-user_group-group_id}}',
            '{{%user_user_group}}',
            'group_id',
            '{{%user_group}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-user_group-user_id}}',
            '{{%user_user_group}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-user_group-user_id}}',
            '{{%user_user_group}}'
        );

        // drops foreign key for table `{{%group}}`
        $this->dropForeignKey(
            '{{%fk-user_group-group_id}}',
            '{{%user_user_group}}'
        );

        // drops index for column `group_id`
        $this->dropIndex(
            '{{%idx-user_group-group_id}}',
            '{{%user_user_group}}'
        );

        $this->dropTable('{{%user_user_group}}');
    }
}
