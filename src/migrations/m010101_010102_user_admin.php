<?php

use yii\db\Migration;

class m010101_010102_user_admin extends Migration
{
    public function up()
    {
        $this->insert('user_user', [
            'id' => '1',
            'username' => 'admin',
            'first_name' => NULL,
            'last_name' => NULL,
            'auth_key' => 'jmsRE--EZf08piRucgAb_XdruHcJ4a5O',
            'password_hash' => '$2y$13$fZes8mOuVjgBLJnwmwf7buLZRkiy51SXSWSmS6BBqSrC4AwNsZiay',
            'password_reset_token' => NULL,
            'email' => 'admin@mail.com',
            'access_token' => 'k40SygDWcgPaS3vtij3d8cRRsz8uQyhf',
            'status' => '10',
            'created_at' => '2022-04-01 08:40:53',
            'updated_at' => '2022-04-01 08:40:53',
        ]);
    }

    public function down()
    {
        $this->delete('user_user', ['id' => '1']);
    }
}