<?php

use portalium\user\Module;
/* @var $this yii\web\View */
/* @var $context portalium\user\components\BaseAuthItemController */
/* @var $model portalium\user\models\auth\AuthItem */

$context = $this->context;
$labels = $context->labels();

$this->title = Module::t( 'Create ' . $labels['Item']);
$this->params['breadcrumbs'][] = ['label' => Module::t( $labels['Items']), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?=
$this->render('_form', [
    'model' => $model,
]);
?>
