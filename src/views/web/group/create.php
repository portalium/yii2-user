<?php

use portalium\user\Module;

/* @var $this yii\web\View */
/* @var $model portalium\user\models\Group */

$this->title = Module::t('Create Group');
$this->params['breadcrumbs'][] = ['label' => Module::t('Groups'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?= $this->render('_form', [
    'model' => $model,
]) ?>
