<?php

use portalium\theme\helpers\Html;
use portalium\theme\widgets\GridView;
use portalium\theme\widgets\Panel;
use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $model portalium\user\models\Group */

$this->title = Module::t('Manage Members for Group: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Module::t('Groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id_group' => $model->id_group]];
$this->params['breadcrumbs'][] = Module::t('Manage Members');

$script = <<< JS
    $("button[type=submit]").click(function(e) {
    var self = $(this),
        tempElement = $("<input type='hidden'/>"),    
        form = $("#" + self.data('formid'));
    tempElement
        .attr("name", this.name)
        .val(self.val())
        .appendTo(form);
    form.submit();
    tempElement.remove();
    e.preventDefault();
});
JS;
$this->registerJs($script);

?>
<?php Panel::begin([
    'title' => Html::encode($this->title),
]) ?>
<div class="members-update">
    <?= Html::errorSummary($model) ?>
    <div class="container">
        <div class="row">
            <div class="col-md-5">
                <div class="group-form">
                    <?= Html::beginForm('', 'post', ['id' => 'availableUsersForm']) ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProviderOutGroup,
                        'filterModel' => $searchModel,
                        'summary' => '',
                        'caption' => Module::t('Available Users'),
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            //'id_group',
                            'username',
                            //'first_name',
                            //'last_name',
                            'email:email',
                            //'password_hash',
                            //'password_reset_token',
                            //'access_token',
                            //'status',
                            //'created_at',
                            //'updated_at',
                            [
                                'class' => 'yii\grid\CheckboxColumn', 'name' => 'addUserIds', 'checkboxOptions' => function ($userModel) use ($model) {
                                    return ['value' => $userModel->id];
                                },
                            ],
                        ],
                    ]); ?>
                    <?= Html::endForm() ?>
                </div>
            </div>


            <div class="col-md-1" style="text-align: center;">
                <br><br>
                <?= Html::submitButton('&gt;&gt;', [
                    'class' => 'btn btn-success',
                    'name' => 'addToGroup',
                    'value' => 1,
                    'title' => Module::t('Add to Group'),
                    'data-formid' => "availableUsersForm"
                ]);
                ?><br><br>
                <?= Html::submitButton('&lt;&lt;', [
                    'class' => 'btn btn-danger',
                    'name' => 'removeFromGroup',
                    'value' => 1,
                    'title' => Module::t('Remove from Group'),
                    'data-formid' => "groupMembersForm"
                ]);
                ?>
            </div>

            <div class="col-md-5">
                <div class="group-form">
                    <?= Html::beginForm('', 'post', ['id' => 'groupMembersForm']) ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProviderInGroup,
                        'filterModel' => $searchModel,
                        'caption' => Module::t('Group Members'),
                        'summary' => '',
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            //'id_group',
                            'username',
                            //'first_name',
                            //'last_name',
                            'email:email',
                            //'password_hash',
                            //'password_reset_token',
                            //'access_token',
                            //'status',
                            //'created_at',
                            //'updated_at',
                            [
                                'class' => 'yii\grid\CheckboxColumn', 'name' => 'removeUserIds', 'checkboxOptions' => function ($userModel) use ($model) {
                                    return ['value' => $userModel->id];
                                },
                            ],
                        ],
                    ]); ?>

                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php Panel::end() ?>
