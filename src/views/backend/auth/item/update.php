<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $context portalium\user\components\BaseAuthItemController */
/* @var $model portalium\user\models\auth\AuthItem */

$context = $this->context;
$labels = $context->labels();
$this->title = Module::t( 'Update ' . $labels['Item']) . ': ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Module::t( $labels['Items']), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->name]];
$this->params['breadcrumbs'][] = Module::t( 'Update');
?>
<?=
$this->render('_form', [
    'model' => $model,
]);
?>

