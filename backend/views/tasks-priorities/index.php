<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\TasksPrioritiesController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TasksPrioritiesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = TasksPrioritiesController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = TasksPrioritiesController::ROOT_LABEL;
?>
<div class="tasks-priorities-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'name',
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
