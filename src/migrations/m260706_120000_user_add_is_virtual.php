<?php

use portalium\db\Migration;
use portalium\user\Module;

class m260706_120000_user_add_is_virtual extends Migration
{
    public function safeUp()
    {
        $this->addColumn(Module::$tablePrefix . 'user', 'is_virtual', $this->tinyInteger(1)->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn(Module::$tablePrefix . 'user', 'is_virtual');
    }
}
