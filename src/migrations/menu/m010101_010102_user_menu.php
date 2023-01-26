<?php

use yii\db\Migration;
use portalium\menu\models\Menu;
use portalium\menu\models\MenuItem;
use portalium\site\models\Form;

class m010101_010102_user_menu extends Migration
{

    public function up()
    {

        $id_menu = Menu::find()->where(['slug' => 'web-menu'])->one()->id_menu;
        $this->insert('menu_item', [
            'id_item' => NULL,
            'label' => 'Users',
            'slug' => 'users',
            'type' => '1',
            'style' => '{"icon":"","color":"","iconSize":""}',
            'data' => '{"type":"1","data":{"route":"#"}}',
            'sort' => '2',
            'id_menu' => $id_menu,
            'name_auth' => 'userWebDefaultIndex',
            'id_user' => 1,
            'date_create' => '2022-06-13 15:30:28',
            'date_update' => '2022-06-13 15:30:28'
        ]);

        $id_item = MenuItem::find()->where(['slug' => 'users'])->one()->id_item;

        $this->batchInsert('menu_item', ['id_item', 'label', 'slug', 'type', 'style', 'data', 'sort', 'id_menu', 'name_auth', 'id_user', 'date_create', 'date_update'], [
            [NULL, 'Groups', 'users-groups', '2', '{"icon":"","color":"","iconSize":""}', '{"type":"2","data":{"module":"user","routeType":"action","route":"\\/user\\/group","model":null,"menuRoute":null,"menuType":"web"}}', '5', $id_menu, 'userWebGroupIndex', 1, '2022-06-13 15:32:26', '2022-06-13 15:32:26'],
            [NULL, 'Users', 'users-users', '2', '{"icon":"","color":"","iconSize":""}', '{"type":"2","data":{"module":"user","routeType":"action","route":"\\/user\\/default\\/index","model":null,"menuRoute":null,"menuType":"web"}}', '6', $id_menu, 'userWebDefaultIndex', 1, '2022-06-13 15:32:26', '2022-06-13 15:32:26'],
        ]);

        $ids = MenuItem::find()->where(['slug' => ['users-groups', 'users-users']])->select('id_item')->column();

        foreach ($ids as $id) {
            $this->insert('menu_item_child', [
                'id_item' => $id_item,
                'id_child' => $id
            ]);
        }
    }

    public function down()
    {
        $ids = $this->db->createCommand('SELECT id_item FROM menu_item WHERE slug in (\'users-permissions\', \'users-roles\', \'users-groups\', \'users-users\')')->queryColumn();

        $this->delete('menu_item', ['id_item' => $ids]);
    }
}
