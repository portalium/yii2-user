<?php

use portalium\theme\helpers\Html;
use portalium\theme\widgets\Modal;
use portalium\user\bundles\UserDeleteAsset;
use portalium\user\Module;
use portalium\widgets\Pjax;
use yii\helpers\Url;

/* @var $this yii\web\View */

UserDeleteAsset::register($this);

Modal::begin([
    'id' => 'modal-user-delete',
    'size' => Modal::SIZE_LARGE,
    'centerVertical' => true,
    'title' => Module::t('Delete User'),
    'options' => [
        'data-manage-url' => Url::to(['/user/default/delete-manage']),
        'data-index-url' => Url::to(['/user/default/index']),
    ],
    'footer' => Html::button(Module::t('Delete'), [
        'class' => 'btn btn-danger',
        'onclick' => 'deleteUser(this)',
    ]),
    'titleOptions' => [
        'style' => 'margin-left: 0px;'
    ],
]);
Pjax::begin([
    'id' => 'user-delete-pjax'
]);
Pjax::end();
Modal::end();
