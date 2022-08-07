<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use portalium\user\Module;
use portalium\theme\widgets\Panel;
use kartik\file\FileInput;
use portalium\user\models\Group;

$this->title = Module::t('Import User');
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
<?php Panel::begin([
    'title' => Module::t('Import User'),
    'actions' => [
        'header' => [],
        'footer' => [
            Html::submitButton(Module::t('Save'), ['class' => 'btn btn-success']),
        ]
    ],
]) ?>


<?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'password')->textInput(['maxlength' => true]) ?>
<?= $form->field($model, 'group')->dropDownList(Group::getGroups(), ['prompt' => Module::t('Not Selected')]) ?>
<?= $form->field($model, 'role')->dropDownList($roles, ['prompt' => Module::t('Not Selected')]) ?>


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