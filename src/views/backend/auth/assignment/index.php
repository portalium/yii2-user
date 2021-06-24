<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('site', 'Assignments');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="assignment-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(); ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' =>  [
            ['class' => 'yii\grid\SerialColumn'],
            'username',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}'
            ],
        ]
    ]);
    ?>
    <?php Pjax::end(); ?>

</div>