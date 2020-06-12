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
        'columns' => [
            'doc_num',
            [
                'attribute' => 'doc_date',
                'label' => 'Дата',
                'format' => ['datetime', 'dd.MM.YYYY г.'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                //'options' => ['width' => '130'],
            ],
            'contractTypeName',
            'organizationName',
            [
                'attribute' => 'doc_date_expires',
                'label' => 'Истекает',
                'format' => ['datetime', 'dd.MM.YYYY г.'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                //'options' => ['width' => '130'],
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
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
