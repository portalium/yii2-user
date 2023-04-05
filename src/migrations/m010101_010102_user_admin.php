<?php

use portalium\db\Migration;

class m010101_010102_user_admin extends Migration
{
    public function up()
    {
        $this->insert('user_user', [
            'id_user' => '1',
            'username' => 'admin',
            'first_name' => NULL,
            'last_name' => NULL,
            'auth_key' => 'jmsRE--EZf08piRucgAb_XdruHcJ4a5O',
            'password_hash' => '$2y$13$fZes8mOuVjgBLJnwmwf7buLZRkiy51SXSWSmS6BBqSrC4AwNsZiay',
            'password_reset_token' => NULL,
            'email' => 'admin@mail.com',
            'access_token' => 'k40SygDWcgPaS3vtij3d8cRRsz8uQyhf',
            'status' => '10',
            'date_create' => '2022-04-01 08:40:53',
            'date_update' => '2022-04-01 08:40:53',
        ]);
    }

    public function down()
    {
        $this->delete('user_user', ['id_user' => '1']);
    }
}