<?php

use yii\widgets\Pjax;
use backend\components\grid\GridView;
use backend\components\grid\TotalsColumn;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
?>
<div class="company-eco-contracts">
    <?php Pjax::begin(['id' => 'pjax-eco-contracts', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped'],
        //'showFooter' => true,
        //'footerRowOptions' => ['class' => 'text-right'],
        'columns' => [
            [
                'attribute' => 'created_at',
                'label' => 'Создан',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            // можно и организации, но они нигде не заполняются
            //'organizationName:ntext:Организация',
            [
                'attribute' => 'date_start',
                'label' => 'Заключен',
                'format' => ['date', 'dd.MM.YYYY'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            [
                'attribute' => 'date_finish',
                'label' => 'Срок',
                'format' => ['date', 'dd.MM.YYYY'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
                //'footer' => '<strong>Итого:</strong>',
            ],
            [
                'class' => TotalsColumn::class,
                'attribute' => 'amount',
                'label' => 'Сумма',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\EcoMc */
                    /* @var $column \yii\grid\DataColumn */

                    return \common\models\FinanceTransactions::getPrettyAmount($model[$column->attribute], 'html');
                },
                'options' => ['width' => '150'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-right'],
                'visible' => false,
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
