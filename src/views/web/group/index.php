<?php

use yii\helpers\Url;
use portalium\user\Module;
use portalium\theme\helpers\Html;
use portalium\theme\widgets\Panel;
use portalium\theme\widgets\GridView;
use portalium\theme\widgets\ActionColumn;

/* @var $this yii\web\View */
/* @var $searchModel portalium\user\models\GroupSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Module::t('Groups');
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Panel::begin([
    'title' => Html::encode($this->title),
    'actions' => [
        'header' => [
            Html::a(Module::t(''), ['create'], ['class' => 'fa fa-plus btn btn-success']),
        ]
    ]
]) ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'name',
            'description:ntext',

            [
                'class' => ActionColumn::class,
                'template' => '{view} {update} {members} {delete}',
                'buttons' => [
                    'members' => function ($url, $model) {
                        return Html::a(
                            Html::tag('i', '', ['class' => 'fa fa-fw fa-user']), 
                            Url::toRoute([$url]),
                            ['class' => 'btn btn-primary btn-xs', 'style' => 'padding: 2px 9px 2px 9px; display: inline-block;'] 
                        );
                    }
                ],
            ],
        ],
        'layout' => '{items}{summary}{pagesizer}{pager}',
    ]); ?>
<?php Panel::end() ?>
