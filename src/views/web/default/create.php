<?php

use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $model portalium\user\models\Group */

$this->title = Module::t('Create User');
$this->params['breadcrumbs'][] = ['label' => Module::t('Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?= $this->render('_form', [
    'model' => $model,
]) ?>
