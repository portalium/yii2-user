<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

use portalium\theme\widgets\GridView;
use portalium\theme\widgets\Panel;
use portalium\user\Module;


/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Module::t( 'Assignments');
$this->params['breadcrumbs'][] = $this->title;

?>
<?php Panel::begin([
    'title' => Module::t('Assignment Users')
]) ?>
    <?php Pjax::begin(); ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => '{items}{pager}{summary}',
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
<?php Panel::end() ?>
