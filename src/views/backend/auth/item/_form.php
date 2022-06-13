<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use portalium\theme\widgets\Panel;
use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $model portalium\user\models\auth\AuthItem */
/* @var $form yii\widgets\ActiveForm */
/* @var $context portalium\user\components\BaseAuthItemController */

$context = $this->context;
$labels = $context->labels();

?>
<?php $form = ActiveForm::begin(['id' => 'item-form']); ?>
<?php Panel::begin([
    'title' => Html::encode($this->title),
    'actions' => [
        'header' => [

        ],
        'footer' => [
            Html::submitButton($model->isNewRecord ? Module::t('Save') : Module::t('Update'), [
                'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
                'name' => 'submit-button'])
        ]
    ]
]) ?>
    <div class="row">
        <div class="col-sm-12">
            <?= $form->field($model, 'name')->textInput(['maxlength' => 64]) ?>

            <?= $form->field($model, 'description')->textarea(['rows' => 2]) ?>
        </div>
    </div>

<?php Panel::end() ?>
<?php ActiveForm::end(); ?>
