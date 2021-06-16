<?php

use portalium\theme\helpers\Html;
use portalium\theme\widgets\GridView;
use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $model portalium\user\models\Group */

$this->title = Module::t('Manage Members for Group: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Module::t('Groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Module::t('Manage Members');

?>
<div class="members-update">

    <?= Html::errorSummary($model) ?>

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="container">
        <div class="row">
            <div class="col-md-6">
            <h4><?= Module::t('Group members') ?></h4>
                <div class="group-form">
                    <?= Html::beginForm() ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProviderInGroup,
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            //'id',
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

                    <div class="form-group">
                        <?= Html::submitButton(Module::t('Remove from Group'), ['name' => 'removeFromGroup', 'value' => 1, 'class' => 'btn btn-success']) ?>
                    </div>


                    <?= Html::endForm() ?>

                </div>
            </div>

            <div class="col-md-6">
            <h4><?= Module::t('Users') ?></h4>
                <div class="group-form">
                    <?= Html::beginForm() ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProviderOutGroup,
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            //'id',
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

                    <div class="form-group">
                        <?= Html::submitButton(Module::t('Add to Group'), ['name' => 'addToGroup', 'value' => 1, 'class' => 'btn btn-success']) ?>
                    </div>


                    <?= Html::endForm() ?>

                </div>
            </div>

        </div>

    </div>
</div>