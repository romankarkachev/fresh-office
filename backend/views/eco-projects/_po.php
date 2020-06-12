<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use backend\components\grid\GridView;
use backend\components\grid\TotalAmountColumn;
use common\models\PaymentOrdersStates;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<hr />
<p class="lead">Оплаты по проекту</p>
<?php Pjax::begin(['id' => 'pjax-po', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

<div class="panel panel-default">
    <div class="panel-body">
        <?= GridView::widget([
            'id' => 'gw-Po',
            'dataProvider' => $dataProvider,
            'layout' => '{items}',
            'columns' => [
                [
                    'label' => 'Создан',
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                        /* @var $model \common\models\PoEp */
                        /* @var $column \yii\grid\DataColumn */

                        return Yii::$app->formatter->asDate($model->po->created_at, 'php:d.m.Y H:i') . ($model->po->is_deleted ? '<i class="fa fa-times btn-block text-danger" aria-hidden="true" title="Элемент помечен на удаление"></i>' : '');
                    },
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'options' => ['width' => '130'],
                ],
                'po.createdByProfileName:ntext:Автор',
                [
                    'label' => 'Статус',
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                        /* @var $model \common\models\PoEp */
                        /* @var $column \yii\grid\DataColumn */

                        if ($model->po->state_id == PaymentOrdersStates::PAYMENT_STATE_ОТКАЗ)
                            return Html::tag('abbr', $model->po->stateName, ['title' => $model->po->comment]);
                        else
                            return $model->po->stateName;
                    },
                ],
                [
                    'attribute' => 'po.eiRepHtml',
                    'format' => 'raw',
                    'footer' => '<strong>Итого:</strong>',
                    'footerOptions' => ['class' => 'text-right'],
                ],
                [
                    'class' => TotalAmountColumn::class,
                    'attribute' => 'po.amount',
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                        /* @var $model \common\models\PoEp */
                        /* @var $column \yii\grid\DataColumn */

                        return \common\models\FinanceTransactions::getPrettyAmount($model->po->amount, 'html');
                    },
                    'options' => ['width' => '150'],
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-right'],
                ],
                [
                    'attribute' => 'po.paid_at',
                    'label' => 'Оплачено',
                    'format' => ['datetime', 'dd.MM.YYYY'],
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'options' => ['width' => '130'],
                ],
            ],
        ]); ?>

    </div>
</div>

<?php Pjax::end(); ?>
