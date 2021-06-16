<?php

use portalium\theme\helpers\Html;
use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $model portalium\user\models\User */

$this->title = Yii::t('site', 'Update User: {name}', [
    'name' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Module::t('Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('site', 'Update');
?>
<div class="user-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
