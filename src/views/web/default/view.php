<?php

use portalium\theme\helpers\Html;
use portalium\theme\widgets\DetailView;
use portalium\theme\widgets\Panel;
use portalium\user\models\User;
use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $model portalium\user\models\User */

$this->title = $model->first_name.' '.$model->last_name;
$this->params['breadcrumbs'][] = ['label' => Module::t('Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php Panel::begin([
    'title' => $model->first_name.' '.$model->last_name.' - '.$model->username,
    'actions' => [
        'header' => [
            Html::a(Module::t(''), ['update', 'id' => $model->id], ['class' => 'fa fa-pencil btn btn-primary']),
            Html::a(Module::t(''), ['delete', 'id' => $model->id], [
                'class' => 'fa fa-trash btn btn-danger',
                'data' => [
                    'confirm' => Module::t( 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]),
        ]
    ]
]) ?>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'username',
            'first_name',
            'last_name',
            'access_token',
            'email:email',
            'date_create',
        ],
    ]) ?>

    <?php
    if (empty($groupNames)) {
        echo Module::t('User does not belong to any group.');
    } else {
        echo Module::t('User groups: ') . implode(", ", $groupNames);
    }
    ?>
<?php Panel::end() ?>
