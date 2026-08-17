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
Panel::begin([
    'title' => Module::t('Users'),
    'actions' => [
        'header' => [
            Html::button(Module::t(''), [
                'class' => 'fa fa-trash btn btn-danger',
                'id' => 'delete-select',
                'type' => 'button',
            ]),
            Html::a(Module::t(''), ['create-virtual'], ['class' => 'fa fa-user-secret btn btn-warning', 'title' => Module::t('Create Virtual User')]),
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
                'attribute' => 'is_virtual',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->is_virtual == User::IS_VIRTUAL_TRUE
                        ? '<span class="badge bg-warning">' . Module::t('Virtual') . '</span>'
                        : '<span class="badge bg-secondary">' . Module::t('Real') . '</span>';
                },
                'filter' => User::getIsVirtualList(),
                'label' => Module::t('Type'),
            ],
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

$indexUrl = Url::to(['index']);
$csrfParam = \Yii::$app->request->csrfParam;
$csrfToken = \Yii::$app->request->csrfToken;
$confirmMsg = json_encode(Module::t('If you continue, all your data will be reset. Do you want to continue?'));

$js = <<< JS
$('#delete-select').on('click', function () {
    var ids = $('input[name="selection[]"]:checked').map(function () { return this.value; }).get();
    if (ids.length === 0) { return; }
    if (!confirm($confirmMsg)) { return; }
    var \$form = $('<form>', { method: 'post', action: '$indexUrl' }).hide().appendTo('body');
    \$form.append($('<input>', { type: 'hidden', name: '$csrfParam', value: '$csrfToken' }));
    $.each(ids, function (i, v) {
        \$form.append($('<input>', { type: 'hidden', name: 'selection[]', value: v }));
    });
    \$form.submit();
});
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