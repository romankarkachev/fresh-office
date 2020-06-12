<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\TendersLossReasonsController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TendersLossReasonsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = TendersLossReasonsController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = TendersLossReasonsController::ROOT_LABEL;
?>
<div class="tenders-loss-reasons-list">
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
