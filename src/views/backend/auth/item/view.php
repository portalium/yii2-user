<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\YiiAsset;
use portalium\theme\widgets\DetailView;
use portalium\theme\widgets\Panel;

use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $context portalium\user\components\BaseAuthItemController */
/* @var $model portalium\user\models\auth\AuthItem */

$context = $this->context;
$labels = $context->labels();
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Module::t($labels['Items']), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

YiiAsset::register($this);
$opts = Json::htmlEncode([
    'items' => $model->getItems(),
    'users' => $model->getUsers(),
    'getUserUrl' => Url::to(['get-users', 'id' => $model->name])
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
    'title' => Html::encode($this->title),
    'actions' => [
        'header' => [
            ($this->context->getType() === 2) ? "" :
                Html::a(Module::t('Update'), ['update', 'id' => $model->name], ['class' => 'btn btn-primary']),
            Html::a(Module::t('Delete'), ['delete', 'id' => $model->name], [
                'class' => 'btn btn-danger',
                'data-confirm' => Module::t('Are you sure to delete this item?'),
                'data-method' => 'post',
            ])
        ]
    ]
]) ?>
<div class="row">
    <div class="col-sm-12">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'name',
                'description:ntext',
            ],
            'template' => '<tr><th style="width:25%">{label}</th><td>{value}</td></tr>',
        ]);
        ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <table class="table table-striped table-bordered">
            <tbody>
                <tr>
                    <th><?= Module::t('Assigned users'); ?></th>
                </tr>
                <tr>
                    <td id="list-users"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-sm-5">
        <input class="form-control search" data-target="available" placeholder="<?= Module::t('Search for available'); ?>">
        <select multiple size="20" class="form-control list" data-target="available"></select>
    </div>
    <div class="col-sm-2" style="text-align: center;">
        <br><br>
        <?= Html::a('&gt;&gt;' . $animateIcon, ['assign', 'id' => $model->name], [
            'class' => 'btn btn-success btn-assign',
            'data-target' => 'available',
            'title' => Module::t('Assign'),
        ]);
        ?><br><br>
        <?= Html::a('&lt;&lt;' . $animateIcon, ['remove', 'id' => $model->name], [
            'class' => 'btn btn-danger btn-assign',
            'data-target' => 'assigned',
            'title' => Module::t('Remove'),
        ]);
        ?>
    </div>
    <div class="col-sm-5">
        <input class="form-control search" data-target="assigned" placeholder="<?= Module::t('Search for assigned'); ?>">
        <select multiple size="20" class="form-control list" data-target="assigned"></select>
    </div>
</div>
<?php Panel::end() ?>