<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;
use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $model portalium\user\models\Group */

$this->title = Yii::t('site', 'Update Group: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('site', 'Groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('site', 'Update');
?>
<div class="group-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="group-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

        <h3><?= Module::t('Manage Group Members') ?></h3>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                'id',
                'username',
                'first_name',
                'last_name',
                'email:email',
                //'password_hash',
                //'password_reset_token',
                //'access_token',
                //'status',
                //'created_at',
                //'updated_at',
                [
                    'class' => 'yii\grid\CheckboxColumn', 'checkboxOptions' => function ($userModel) use ($model) {
                        return ['value' => $userModel->id, 'checked' => in_array($userModel->id, $model->getCurrentUserIds())];
                    },
                ],
            ],
        ]); ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('site', 'Save'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>