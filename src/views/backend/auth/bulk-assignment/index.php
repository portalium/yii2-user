<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\YiiAsset;

use portalium\theme\widgets\Panel;
use portalium\user\Module;

/* @var $this yii\web\View */


$itemType = null;

switch ($model->getItem()->type) {
    case 1:
        $itemType = Module::t('Roles');
        $itemUrl = '/user/auth/role';
        break;
    case 2:
        $itemType = Module::t('Permissions');
        $itemUrl = '/user/auth/permission';
        break;
}

$this->title = Module::t('Bulk Assignment') . ' : ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => $itemType, 'url' => [$itemUrl]];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => [$itemUrl . '/view', 'id' => $model->name]];
$this->params['breadcrumbs'][] = Module::t('Bulk Assignment');

YiiAsset::register($this);

$opts = Json::htmlEncode([
    'items' => $model->getItems(),
    'users' => $userDataProvider->query->select(['id', 'username'])->all(),
    'groups' => $groupDataProvider->query->select(['id', 'name'])->all(),
    'assignedUsers' => $assignedUsers
]);

$optgroupLabels = Json::htmlEncode([
    'allUsers' => Module::t('All Users'),
    'allGroups' => Module::t('All Groups'),
    'assignedUsers' => Module::t('Assigned Users')
]);

$this->registerJs("var _opts = {$opts};");
$this->registerJs("var optgroupLabels = {$optgroupLabels};");

$this->registerJs($this->render('_script.js'));
$animateIcon = ' <i class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></i>';
?>
<?php Panel::begin([
    'title' => $this->title
]) ?>
<div class="row">
    <div class="col-sm-5">
        <input class="form-control search" data-target="available" placeholder="<?= Module::t('Search for available'); ?>">
        <select multiple size="20" class="form-control list" data-target="available">
        </select>
    </div>
    <div class="col-sm-2" style="text-align: center">
        <br><br>
        <?= Html::a('&gt;&gt;' . $animateIcon, ['assign', 'id' => (string) $model->name], [
            'class' => 'btn btn-success btn-assign',
            'title' => Module::t('Assign'),
        ]); ?><br><br>
        <?= Html::a('&lt;&lt;' . $animateIcon, ['revoke', 'id' => (string) $model->name], [
            'class' => 'btn btn-danger btn-assign',
            'title' => Module::t('Remove'),
        ]); ?>
    </div>
    <div class="col-sm-5">
        <input class="form-control search" data-target="assigned" placeholder="<?= Module::t('Search for assigned'); ?>">
        <select multiple size="20" class="form-control list" data-target="assigned">
        </select>
    </div>
</div>
<?php Panel::end() ?>