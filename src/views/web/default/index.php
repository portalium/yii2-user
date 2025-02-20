<?php

use yii\helpers\Url;
use portalium\user\Module;
use portalium\user\models\User;
use portalium\theme\helpers\Html;
use portalium\theme\widgets\Panel;
use portalium\theme\widgets\GridView;
use portalium\theme\widgets\ActiveForm;
use portalium\theme\widgets\ActionColumn;
use portalium\site\helpers\ActiveForm as HelpersActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel portalium\user\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Module::t('Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<?php
$form = ActiveForm::begin();
Panel::begin([
    'title' => Module::t('Users'),
    'actions' => [
        'header' => [
            Html::submitButton(Module::t(''), [
                'class' => 'fa fa-trash btn btn-danger', 'id' => 'delete-select',
                'data' => [
                    'confirm' => Module::t('If you continue, all your data will be reset. Do you want to continue?'),
                    'method' => 'post'

                ]
            ]),
            Html::a(Module::t(''), ['create'], ['class' => 'fa fa-plus btn btn-success']),
        ]
    ]
]) ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); 



    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'class' => 'yii\grid\CheckboxColumn'
            ],
            'username',
            'first_name',
            'last_name',
            'email:email',
            [
                'class' => ActionColumn::class,
                'template' => '{view} {update} {assignment} {delete}',
                'buttons' => [
                    'assignment' => function ($url, $model) {
                        return Html::a(
                            Html::tag('i', '', ['class' => 'fa fa-fw fa-lock']), 
                            Url::toRoute(['/rbac/assignment/view', 'id' => $model->id]),
                            ['class' => 'btn btn-primary btn-xs', 'style' => 'padding: 2px 9px 2px 9px; display: inline-block;'] 
                        );
                    }
                ]
            ],
        ],
        'layout' => '{items}{summary}{pagesizer}{pager}',
    ]); ?>
<?php Panel::end();
ActiveForm::end();
?>