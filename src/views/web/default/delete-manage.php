<?php

use portalium\theme\helpers\Html;
use portalium\theme\widgets\ActiveForm;
use portalium\theme\widgets\Panel;
use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $model portalium\user\models\User */
/* @var $form portalium\theme\widgets\ActiveForm */

?>
<?php $form = ActiveForm::begin(
    [
        'id' => 'delete-form',
        'action' => ['delete-manage', 'id' => $id_user],
        'options' => [
            'data-pjax' => true,
            'class' => 'form-horizontal'
        ]
    ]
); ?>

<div class="mb-3">
    <div class="alert alert-warning">
        <?= Module::t('When this user is deleted, records in the selected modules will be <strong>permanently deleted</strong>. Unchecked modules will be transferred to the selected user below.') ?>
    </div>
</div>

<div class="mb-4">
    <label class="form-label fw-bold"><?= Module::t('Select Modules to Delete') ?></label>
    <div class="form-check-list border rounded p-3">
        <?= $form->field($model, 'modules', ['template' => "{input}"])->checkboxList(
            $modules,
            [
                'itemOptions' => ['class' => 'form-check-input me-2'],
                'class' => 'form-check'
            ]
        ) ?>
    </div>
</div>

<div class="mb-3">
    <?= $form->field($model, 'default_user')->dropDownList(
        $users,
        ['class' => 'form-select']
    )->label(Module::t('Transfer remaining data to')) ?>
</div>
<?php ActiveForm::end(); ?>