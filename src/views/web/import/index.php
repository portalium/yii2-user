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


    <?= $form->field($model, 'username')->dropDownList([], ['prompt' => Module::t('Select Column'), 'id' => 'username-dropdown']) ?>
    <?= $form->field($model, 'first_name')->dropDownList([], ['prompt' => Module::t('Select Column'), 'id' => 'first-name-dropdown']) ?>
    <?= $form->field($model, 'last_name')->dropDownList([], ['prompt' => Module::t('Select Column'), 'id' => 'last-name-dropdown']) ?>
    <?= $form->field($model, 'email')->dropDownList([], ['prompt' => Module::t('Select Column'), 'id' => 'email-dropdown']) ?>
    <?= $form->field($model, 'password')->dropDownList([], ['prompt' => Module::t('Select Column'), 'id' => 'password-dropdown']) ?>
    <?= $form->field($model, 'group')->dropDownList(Group::getGroups(), ['prompt' => Module::t('Not Selected')]) ?>
    <?= $form->field($model, 'role')->dropDownList($roles, ['prompt' => Module::t('Not Selected')]) ?>


<div id="file-input-section">
    <?= FileInput::widget([
        'model' => $model,
        'attribute' => 'file',
        'options' => [
            'multiple' => false,
            'accept' => 'doc/*',
            'id' => 'file-input'
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

<?php
$this->registerJs('
    $("#file-input").on("fileloaded", function(event, file, previewId, index, reader) {
        var formData = new FormData();
        formData.append("file", file);
        formData.append("_csrf-web", yii.getCsrfToken());
        $.ajax({
            url: "/user/import/get-column", 
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                var columns = response.columns;
                updateDropdown("#username-dropdown", columns);
                updateDropdown("#first-name-dropdown", columns);
                updateDropdown("#last-name-dropdown", columns);
                updateDropdown("#email-dropdown", columns);
                updateDropdown("#password-dropdown", columns);
            },
            error: function() {
                alert("Error occurred while fetching column names.");
            }
        });
    });

    function updateDropdown(dropdownId, columns) {
        $(dropdownId).empty();
        $(dropdownId).append("<option value=\"\">' . Module::t('Select Column') . '</option>");
        $.each(columns, function(index, value) {
            $(dropdownId).append("<option value=\"" + value + "\">" + value + "</option>");
        });
    }

');
?>