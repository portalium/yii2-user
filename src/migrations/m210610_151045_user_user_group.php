<?php

use portalium\db\Migration;
use portalium\user\Module;

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

        $this->createTable('{{%' . Module::$tablePrefix . 'user_group}}', [
            'id_group' => $this->primaryKey(),
            'id_user' => $this->integer()->notNull(),
            'id_group' => $this->integer()->notNull(),
            'date_create' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        // creates index for column `id_user`
        $this->createIndex(
            '{{%idx-user_group-id_user}}',
            '{{%' . Module::$tablePrefix . 'user_group}}',
            'id_user'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-user_group-id_user}}',
            '{{%' . Module::$tablePrefix . 'user_group}}',
            'id_user',
            '{{%' . Module::$tablePrefix . 'user}}',
            'id_user',
            'RESTRICT'
        );

        // add foreign key for table `{{%group}}`
        $this->addForeignKey(
            '{{%fk-user_group-id_group}}',
            '{{%' . Module::$tablePrefix . 'user_group}}',
            'id_group',
            '{{%' . Module::$tablePrefix . 'group}}',
            'id_group',
            'RESTRICT'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-user_group-id_user}}',
            '{{%' . Module::$tablePrefix . 'user_group}}'
        );

        // drops index for column `id_user`
        $this->dropIndex(
            '{{%idx-user_group-id_user}}',
            '{{%' . Module::$tablePrefix . 'user_group}}'
        );

        // drops foreign key for table `{{%group}}`
        $this->dropForeignKey(
            '{{%fk-user_group-id_group}}',
            '{{%' . Module::$tablePrefix . 'user_group}}'
        );

        $this->dropTable('{{%' . Module::$tablePrefix . 'user_group}}');
    }
}
