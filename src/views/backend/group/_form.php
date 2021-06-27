<?php

use portalium\theme\helpers\Html;
use portalium\theme\widgets\ActiveForm;
use portalium\theme\widgets\Panel;
use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $model portalium\user\models\Group */

?>
<?php $form = ActiveForm::begin(); ?>
<?php Panel::begin([
    'title' => Module::t('User Details'),
    'actions' => [
        'header' => [
            Html::submitButton(Yii::t('site', 'Save'), ['class' => 'btn btn-success']),
        ]
    ]
]) ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
<?php Panel::end() ?>
<?php ActiveForm::end(); ?>
