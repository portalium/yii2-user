<?php

use portalium\theme\helpers\Html;
use portalium\theme\widgets\GridView;
use portalium\theme\widgets\Panel;
use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $searchModel portalium\user\models\GroupSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Module::t('Groups');
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Panel::begin([
    'title' => Module::t('Settings'),
    'actions' => [
        'header' => [
            Html::a(Module::t('Create Group'), ['create'], ['class' => 'btn btn-success']),
        ]
    ]
]) ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => '{items}{pager}{summary}',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'description:ntext',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {members} {delete}',
                'buttons' => [
                    'members' => function ($url, $model) {
                        return Html::a(Html::tag('i', '', ['class' => 'fa fa-fw fa-user']), $url, ['title' => Module::t('Manage Members')]);
                    }
                ],
            ],
        ],
    ]); ?>
<?php Panel::end() ?>
