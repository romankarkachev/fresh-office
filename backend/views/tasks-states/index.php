<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\TasksStatesController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TasksStatesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = TasksStatesController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = TasksStatesController::ROOT_LABEL;
?>
<div class="tasks-states-list">
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
