<?php

use portalium\theme\helpers\Html;
use portalium\theme\widgets\DetailView;
use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $model portalium\user\models\User */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Module::t('Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('site', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('site', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('site', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'username',
            'first_name',
            'last_name',
            'auth_key',
            'password_hash',
            'password_reset_token',
            'email:email',
            'access_token',
            'status',
            'created_at',
            'updated_at',
        ],
    ]) ?>

    <?php
    if (empty($groupNames)) {
        echo Module::t('User does not belong to any group.');
    } else {
        echo Module::t('User groups: ') . implode(", ", $groupNames);
    }
    ?>


</div>