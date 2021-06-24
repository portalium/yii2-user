<?php

use yii\helpers\Html;
use yii\grid\GridView;


/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $context portalium\user\components\BaseAuthItemController */
/* @var $searchModel portalium\user\models\auth\search\AuthItemSearch */

$context = $this->context;
$labels = $context->labels();
$this->title = Yii::t('site', $labels['Items']);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="role-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <?php if ($this->context->getType() !== 2) : ?>
        <?= Html::a(Yii::t('site', 'Create ' . $labels['Item']), ['create'], ['class' => 'btn btn-success']);
        endif; ?>
    </p>
    <?php
    $buttonsKeyArray = [];
    if ($this->context->getType() === 2) {
        $buttonsKeyArray['delete'] = function ($url, $model) {
            return null;
        };
        $buttonsKeyArray['update'] = function ($url, $model) {
            return null;
        };
    }
    ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn'
            ],
            [
                'attribute' => 'name',
                'label' => Yii::t('site', 'Name'),
            ],
            [
                'attribute' => 'description',
                'label' => Yii::t('site', 'Description'),
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'buttons' => $buttonsKeyArray
            ],
        ],
    ])
    ?>

</div>