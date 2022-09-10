<?php

use portalium\theme\helpers\Html;
use portalium\theme\widgets\ActiveForm;
use portalium\theme\widgets\Panel;
use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $model portalium\user\models\User */
/* @var $form portalium\theme\widgets\ActiveForm */
?>
<?php $form = ActiveForm::begin(); ?>
<?php Panel::begin([
    'title' => Html::encode($this->title),
    'actions' => [
        'header' => [
        ],
        'footer' => [
            Html::submitButton(Module::t( 'Save'), ['class' => 'btn btn-success']),
        ]
    ],
]) ?>
    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
    <?php if($model->isNewRecord): ?>
        <?= $form->field($model, 'password')->passwordInput() ?>
    <?php endif; ?>
<?php Panel::end() ?>
<?php ActiveForm::end(); ?>
