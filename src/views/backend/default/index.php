<?php

use portalium\site\helpers\ActiveForm as HelpersActiveForm;
use portalium\theme\helpers\Html;
use portalium\theme\widgets\GridView;
use portalium\theme\widgets\Panel;
use portalium\user\Module;
use portalium\theme\widgets\ActiveForm;

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
            Html::submitButton(Module::t('Delete Selected Items'), [
                'class' => 'btn btn-danger', 'id' => 'delete-select',
                'data' => [
                    'confirm' => Module::t('If you continue, all your data will be reset. Do you want to continue?'),
                    'method' => 'post'

                ]
            ]),
            Html::a(Module::t('Create User'), ['create'], ['class' => 'btn btn-success']),
        ]
    ]
]) ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); 



    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => '{items}{pager}{summary}',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'class' => 'yii\grid\CheckboxColumn'
            ],
            'id',
            'username',
            'first_name',
            'last_name',
            'email:email',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {assignment} {delete}',
                'buttons' => [
                    'assignment' => function ($url, $model) {
                        return Html::a(Html::tag('i', '', ['class' => 'fa fa-fw fa-lock']), ['/user/auth/assignment/view', 'id' => $model->id], ['title' => Module::t('Assignment')]);
                    }
                ]
            ],
        ],
    ]); ?>
<?php Panel::end();
ActiveForm::end();
?>