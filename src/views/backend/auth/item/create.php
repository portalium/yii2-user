<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $context portalium\user\components\BaseAuthItemController */
/* @var $model portalium\user\models\auth\AuthItem */

$context = $this->context;
$labels = $context->labels();

$this->title = Yii::t('site', 'Create ' . $labels['Item']);
$this->params['breadcrumbs'][] = ['label' => Yii::t('site', $labels['Items']), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?=
$this->render('_form', [
    'model' => $model,
]);
?>
