<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\captcha\Captcha;
use portalium\site\Module;
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
            Html::submitButton(Module::t('Import Users'), ['class' => 'btn btn-success'])
        ]
    ]
]) ?>

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

