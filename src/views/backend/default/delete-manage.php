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
            'title' => Module::t('Users'),
            'actions' => [
                'header' => [
                    Html::a(Module::t('Delete User'), ['delete-manage', 'id' => $id_user], [
                        'class' => 'btn btn-danger',
                        'data-confirm' => Module::t('Are you sure you want to delete this item?'),
                        'data-method' => 'post',
                    ]),
                ]
            ]
        ]) 
    ?>
        <?= $form->field($model, 'modules')->checkboxList($modules)->label(Module::t('Warning: When the user is deleted, his records in the marked modules will also be deleted. Tick the records you want to be deleted.')) ?>
        <?php
            echo Html::tag('div', Module::t('Note: Unchecked Records will be assigned to the default user.'), ['class' => 'alert alert-warning']);
        ?>
        <?= $form->field($model, 'default_user')->dropDownList($users)->label(Module::t('Default User')) ?>
    <?php Panel::end() ?>
<?php ActiveForm::end(); ?>
