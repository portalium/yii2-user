<?php

use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\YiiAsset;

/* @var $this yii\web\View */

$userName = $model->username;
$userName = Html::encode($userName);

$this->title = Yii::t('site', 'Assignment') . ' : ' . $userName;

$this->params['breadcrumbs'][] = ['label' => Yii::t('site', 'Assignments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $userName;

YiiAsset::register($this);
$opts = Json::htmlEncode([
    'items' => $model->getItems(),
]);
$this->registerJs("var _opts = {$opts};");
$this->registerJs($this->render('_script.js'));
$animateIcon = ' <i class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></i>';
?>
<div class="assignment-index">
    <h1><?= $this->title; ?></h1>

    <div class="row">
        <div class="col-sm-5">
            <input class="form-control search" data-target="available" placeholder="<?= Yii::t('site', 'Search for available'); ?>">
            <select multiple size="20" class="form-control list" data-target="available">
            </select>
        </div>
        <div class="col-sm-1">
            <br><br>
            <?= Html::a('&gt;&gt;' . $animateIcon, ['assign', 'id' => (string) $model->id], [
                'class' => 'btn btn-success btn-assign',
                'data-target' => 'available',
                'title' => Yii::t('site', 'Assign'),
            ]); ?><br><br>
            <?= Html::a('&lt;&lt;' . $animateIcon, ['revoke', 'id' => (string) $model->id], [
                'class' => 'btn btn-danger btn-assign',
                'data-target' => 'assigned',
                'title' => Yii::t('site', 'Remove'),
            ]); ?>
        </div>
        <div class="col-sm-5">
            <input class="form-control search" data-target="assigned" placeholder="<?= Yii::t('site', 'Search for assigned'); ?>">
            <select multiple size="20" class="form-control list" data-target="assigned">
            </select>
        </div>
    </div>
</div>