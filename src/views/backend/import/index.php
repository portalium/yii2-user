<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use portalium\user\Module;
use portalium\theme\widgets\Panel;
use kartik\file\FileInput;

$this->title = Module::t('Index');
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
<?php Panel::begin([
    'title' => Module::t('Import Users'),
    'actions' => [
        'header' => [
            Html::submitButton(Module::t('Save'), ['class' => 'btn btn-success'])
        ]
    ]
]) ?>
<div class="row">
    <div class="col-md-3">
        <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'style' => 'width:140px']) ?>
        <?= $form->field($model, 'first_name')->textInput(['maxlength' => true, 'style' => 'width:140px']) ?>
        <?= $form->field($model, 'last_name')->textInput(['maxlength' => true, 'style' => 'width:140px']) ?>
    </div>
    <div class="col-md-3">
        <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'style' => 'width:140px']) ?>
        <?= $form->field($model, 'password')->textInput(['maxlength' => true, 'style' => 'width:140px']) ?>
    </div>
</div>
<div id="internal">
    <?= FileInput::widget([
        'model' => $model,
        'attribute' => 'file',
        'options' => [
            'multiple' => false,
            'accept' => 'doc/*'
        ],
        'pluginOptions' => [
            'allowedFileExtensions' => ['csv'],
            'showPreview' => true,
            'showCaption' => true,
            'showRemove' => true,
            'showUpload' => false,
            'initialCaption' => Module::t('Select Files'),
            'initialPreviewAsData' => true,
            'initialPreview' => false,
            'overwriteInitial' => true,
            'maxFileCount' => 10
        ]
    ]) ?>
</div>
<?php Panel::end() ?>
<?php ActiveForm::end(); ?>