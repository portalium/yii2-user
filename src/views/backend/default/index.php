<?php

use portalium\theme\helpers\Html;
use portalium\theme\widgets\GridView;
use portalium\theme\widgets\Panel;
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
            Html::submitButton(Module::t('Delete Selected User'), ['class' => 'btn btn-success', 'id' => 'delete-select']),
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
            //'auth_key',
            //'password_hash',
            //'password_reset_token',
            'email:email',
            //'access_token',
            //'status',
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

<?php
$script = <<< JS
    $("#delete-select").on("click", function(e){
       e.preventDefault()
       var keys = $("#w1").yiiGridView("getSelectedRows");
       $.ajax({
         url: "/admin/user/default/multiple-delete",
         type: "POST",
         data: {id: keys},
       })
   });
JS;
$this->registerJs($script, yii\web\View::POS_READY);


?>