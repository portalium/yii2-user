<?php

use portalium\theme\helpers\Html;
use portalium\theme\widgets\GridView;
use portalium\theme\widgets\Panel;
use portalium\user\models\User;
use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $searchModel portalium\user\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Module::t('Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Panel::begin([
    'title' => Module::t('Users'),
    'actions' => [
        'header' => [
            Html::a(Module::t(''), ['create'], ['class' => 'fa fa-plus btn btn-success']),
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
                'attribute' => 'first_name',
                'value' => function ($model) {
                    return $model->first_name . ' ' . $model->last_name;
                },

            ],
            'username',
            //'auth_key',
            //'password_hash',
            //'password_reset_token',
            'email:email',
            //'access_token',
            [
                'attribute' => 'status',
                'label' => Module::t('Status'),
                'value' => function ($model) {
                    return $model->status == 10 ? Module::t('Active') : Module::t('Passive');
                },
            ],
            //'created_at',
            //'updated_at',

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
<?php Panel::end() ?>
