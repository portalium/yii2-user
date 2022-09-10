<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\YiiAsset;

use portalium\theme\widgets\Panel;
use portalium\user\Module;

/* @var $this yii\web\View */

$userName = $model->username;
$firstName = $model->first_name;
$lastName = $model->last_name;

$userName = Html::encode($userName);

$this->title = Module::t('Assignment') . ' : ' . $model->first_name . ' ' . $model->last_name;

$this->params['breadcrumbs'][] = ['label' => Module::t('Users'), 'url' => ['/user']];
$this->params['breadcrumbs'][] = $userName;

YiiAsset::register($this);
$opts = Json::htmlEncode([
    'items' => $model->getItems(),
]);

$optgroupLabels = Json::htmlEncode([
    'roles' => Module::t('Roles'),
    'permissions' => Module::t('Permissions'),
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
        <?= Html::a('&gt;&gt;' . $animateIcon, ['assign', 'id' => (string) $model->id], [
            'class' => 'btn btn-success btn-assign',
            'data-target' => 'available',
            'title' => Module::t('Assign'),
        ]); ?><br><br>
        <?= Html::a('&lt;&lt;' . $animateIcon, ['revoke', 'id' => (string) $model->id], [
            'class' => 'btn btn-danger btn-assign',
            'data-target' => 'assigned',
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