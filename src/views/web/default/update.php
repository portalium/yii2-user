<?php

use portalium\theme\helpers\Html;
use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $model portalium\user\models\User */

$this->title = Module::t('Update User: '.$model->first_name.' '.$model->last_name);
$this->params['breadcrumbs'][] = ['label' => Module::t('Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->first_name . " " . $model->last_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Module::t('Update');
?>
<?= $this->render('_form', [
    'model' => $model,
]) ?>
