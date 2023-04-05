<?php

use portalium\theme\helpers\Html;
use portalium\theme\widgets\DetailView;
use portalium\theme\widgets\Panel;
use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $model portalium\user\models\Group */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Module::t('Groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Panel::begin([
    'title' => Html::encode($this->title),
    'actions' => [
        'header' => [
            Html::a(Module::t(''), ['update', 'id' => $model->id_group], ['class' => 'fa fa-pencil btn btn-success']),
            Html::a(Module::t(''), ['members', 'id' => $model->id_group], ['class' => 'fa fa-user btn btn-primary']),
            Html::a(Module::t(''), ['delete', 'id' => $model->id_group], [
                'class' => 'fa fa-trash btn btn-danger',
                'data' => [
                    'confirm' => Module::t('Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ])
        ]
    ]
]) ?>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'name',
            'description:ntext',
            'date_create',
        ],
    ]) ?>

    <?php
    if (empty($userNames)) {
        echo Module::t('There is no user in the group.');
    } else {
        echo Module::t('Users: ') . implode(", ", $userNames);
    }
    ?>
<?php Panel::end() ?>
