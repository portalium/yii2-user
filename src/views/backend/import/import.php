<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\captcha\Captcha;
use portalium\site\Module;
use portalium\theme\widgets\Panel;
use kartik\file\FileInput;

$this->title = Module::t('Import');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
            <?php Panel::begin([
                'title' => Module::t('Create User'),
                'actions' => [
                    'header' => [
                        Html::submitButton(Module::t(''), ['class' => 'btn btn-success fa fa-save'])
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
        </div>
    </div>
</div>
