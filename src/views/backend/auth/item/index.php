<?php

use yii\helpers\Html;
use portalium\theme\widgets\GridView;
use portalium\theme\widgets\Panel;
use portalium\user\Module;


/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $context portalium\user\components\BaseAuthItemController */
/* @var $searchModel portalium\user\models\auth\search\AuthItemSearch */

$context = $this->context;
$labels = $context->labels();
$this->title = Module::t($labels['Items']);
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Panel::begin([
    'title' => Html::encode($this->title),
    'actions' => [
        'header' => [
            ($this->context->getType() === 2) ? "": Html::a(Module::t('Create ' . $labels['Item']), ['create'], ['class' => 'btn btn-success'])
        ]
    ]
]) ?>
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
        'layout' => '{items}{pager}{summary}',
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn'
            ],
            [
                'attribute' => 'name',
                'label' => Module::t( 'Name'),
            ],
            [
                'attribute' => 'description',
                'label' => Module::t( 'Description'),
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'buttons' => $buttonsKeyArray
            ],
        ],
    ])
    ?>
<?php Panel::end() ?>
