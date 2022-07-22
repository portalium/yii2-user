<?php

use portalium\menu\models\Menu;
use portalium\menu\models\MenuItem;
use yii\db\Migration;
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
            'id_parent' => '0',
            'id_menu' => $id_menu,
            'name_auth' => 'userWebDefaultIndex',
            'date_create' => '2022-06-13 15:30:28',
            'date_update' => '2022-06-13 15:30:28'
        ]);

        $idParent = MenuItem::find()->where(['slug' => 'users'])->one()->id_item;

        $this->batchInsert('menu_item', ['id_item', 'label', 'slug', 'type', 'style', 'data', 'sort', 'id_parent', 'id_menu', 'name_auth', 'date_create', 'date_update'], [
            [NULL, 'Permissions', 'users-permissions', '2', '{"icon":"","color":"","iconSize":""}', '{"type":"2","data":{"module":"user","routeType":"action","route":"\\/user\\/auth\\/permission","model":null,"menuRoute":null,"menuType":"web"}}', '3', $idParent, $id_menu, 'userWebPermissionViewPath', '2022-06-13 15:32:26', '2022-06-13 15:32:26'],
            [NULL, 'Roles', 'users-roles', '2', '{"icon":"","color":"","iconSize":""}', '{"type":"2","data":{"module":"user","routeType":"action","route":"\\/user\\/auth\\/role","model":null,"menuRoute":null,"menuType":"web"}}', '4', $idParent, $id_menu, 'userWebRoleViewPath', '2022-06-13 15:32:26', '2022-06-13 15:32:26'],
            [NULL, 'Groups', 'users-groups', '2', '{"icon":"","color":"","iconSize":""}', '{"type":"2","data":{"module":"user","routeType":"action","route":"\\/user\\/group","model":null,"menuRoute":null,"menuType":"web"}}', '5', $idParent, $id_menu, 'userWebGroupIndex', '2022-06-13 15:32:26', '2022-06-13 15:32:26'],
            [NULL, 'Users', 'users-users', '2', '{"icon":"","color":"","iconSize":""}', '{"type":"2","data":{"module":"user","routeType":"action","route":"\\/user\\/default\\/index","model":null,"menuRoute":null,"menuType":"web"}}', '6', $idParent, $id_menu, 'userWebDefaultIndex', '2022-06-13 15:32:26', '2022-06-13 15:32:26'],
            [NULL, 'Import User', 'users-import', '2', '{"icon":"","color":"","iconSize":""}', '{"type":"2","data":{"module":"user","routeType":"action","route":"\\/user\\/import\\/index,"model":null,"menuRoute":null,"menuType":"web"}}', '11', $idParent, $id_menu, 'userWebImportIndex', '2022-07-22 15:32:26', ''],
        ]);
    }

    public function down()
    {
        $ids = $this->db->createCommand('SELECT id_item FROM menu_item WHERE slug in (\'users-permissions\', \'users-roles\', \'users-groups\', \'users-users\')')->queryColumn();

        $this->delete('menu_item', ['id_item' => $ids]);
    }
}
