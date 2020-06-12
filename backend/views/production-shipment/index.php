<?php

use common\models\ProductionShipment;
use yii\helpers\Html;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ProductionShipmentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = ProductionShipment::LABEL_ROOT . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ProductionShipment::LABEL_ROOT;
?>
<div class="production-shipment-list">
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'created_at',
                'label' => 'Создан',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            [
                'attribute' => 'createdByProfileName',
                'options' => ['width' => '200'],
            ],
            'rn',
            'ferrymanName',
            'transportRep',
            'siteName',
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
