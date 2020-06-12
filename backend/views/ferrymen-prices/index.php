<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use common\models\FinanceTransactions;
use backend\controllers\FerrymenPricesController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\FerrymenPricesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = FerrymenPricesController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = FerrymenPricesController::ROOT_LABEL;
?>
<div class="ferrymen-prices-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'ferrymanName',
            [
                'attribute' => 'price',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\FerrymenPrices */
                    /* @var $column \yii\grid\DataColumn */

                    return FinanceTransactions::getPrettyAmount($model->{$column->attribute}, 'html');
                },
                'options' => ['width' => '150'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-right'],
            ],
            [
                'attribute' => 'cost',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\FerrymenPrices */
                    /* @var $column \yii\grid\DataColumn */

                    return FinanceTransactions::getPrettyAmount($model->{$column->attribute}, 'html');
                },
                'options' => ['width' => '150'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-right'],
            ],
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
