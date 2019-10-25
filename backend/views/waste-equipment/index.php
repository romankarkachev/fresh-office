<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\WasteEquipmentController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\WasteEquipmentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = WasteEquipmentController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = WasteEquipmentController::ROOT_LABEL;
?>
<div class="waste-equipment-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'columns' => [
            'name',
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
