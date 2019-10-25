<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\TasksTypesController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TasksTypesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = TasksTypesController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = TasksTypesController::ROOT_LABEL;
?>
<div class="tasks-types-list">
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
