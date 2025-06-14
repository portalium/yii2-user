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
use portalium\theme\widgets\Modal;
use portalium\widgets\Pjax;

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
                'class' => 'fa fa-trash btn btn-danger',
                'id' => 'delete-select',
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
            [
                'class' => 'portalium\grid\CheckboxColumn'
            ],
            ['class' => 'portalium\grid\SerialColumn'],
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
                    },
                    'delete' => function ($url, $model) {
                        return Html::button(Html::tag('i', '', ['class' => 'fa fa-fw fa-trash']), [
                            'class' => 'btn btn-danger btn-xs',
                            'style' => 'padding: 2px 9px 2px 9px; display: inline-block;',
                            'title' => Module::t('Delete'),
                            'onclick' => 'openDeleteModal(this, ' . $model->id . ')',
                        ]);
                    },
                ]
            ],
        ],
        'layout' => '{items}{summary}{pagesizer}{pager}',
    ]); ?>
<?php Panel::end();
ActiveForm::end();

Modal::begin([
    'id' => 'modal-user-delete',
    'size' => Modal::SIZE_LARGE,
    'centerVertical' => true,
    'title' => Module::t('Delete User'),
    'footer' => Html::button(Module::t('Delete'), [
        'class' => 'btn btn-danger',
        'onclick' => 'deleteUser(this)',
    ]),
    'titleOptions' => [
        'style' => 'margin-left: 0px;'
    ],
]);
Pjax::begin([
    'id' => 'user-delete-pjax'
]);

Pjax::end();
Modal::end();

$js = <<< JS
function openDeleteModal(e, id) {
    $.pjax.reload({
        container: '#user-delete-pjax',
        url: '/user/default/delete-manage?id=' + id,
        type: 'GET',
        push: false,
        replace: false,
        timeout: 10000
    }).done(function() {
        $('#modal-user-delete').modal('show');
    });
}
function deleteUser(e) {
    var form = $('#user-delete-pjax').find('form');
    var formData = form.serialize();
    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        success: function (data) {
        },
        error: function (data) {
        },
        complete: function (data) {
            location.reload();
        }
    });
}
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>