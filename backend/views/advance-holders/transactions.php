<?php

use backend\controllers\AdvanceHoldersController;
use backend\components\grid\TotalAmountColumn;
use common\models\FinanceTransactions;

/* @var $this yii\web\View */
/* @var $searchModel common\models\FinanceAdvanceHoldersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $renderRestricted bool признак, определяющий расширенное или ограниченное использование инструмента */

$this->title = 'Взаиморасчеты с подотчетными лицами | ' . Yii::$app->name;
if ($renderRestricted) {
    // вывод для пользователей с ограниченными возможностями в этом разделе
    $this->params['breadcrumbs'][] = AdvanceHoldersController::ROOT_LABEL;
}
else {
    // вывод для пользователей, имеющих полный доступ к инструментам раздела
    $this->params['breadcrumbs'][] = AdvanceHoldersController::ROOT_BREADCRUMB;
    $this->params['breadcrumbs'][] = $searchModel->userProfileName;
}

$balanceMsg = '';
$balance = $searchModel->user->advanceBalanceCalculated;
if (false !== $balance) {
    if ($balance == 0) {
        $balanceMsg = 'Задолженность отсутствует';
    }
    else {
        if ($renderRestricted) {
            $balanceMsg = $balance < 0 ? 'Ваша переплата составляет' : 'Ваша задолженность';
        }
        else {
            $balanceMsg = $balance < 0 ? 'Задложенность перед сотрудником' : 'Задолженность сотрудника';
        }
        $balanceMsg .= ': ' . FinanceTransactions::getPrettyAmount($balance, 'html');
    }
}
?>
<div class="finance-advance-holders-list">
    <?= $this->render('_search_transactions', ['model' => $searchModel, 'renderRestricted' => $renderRestricted]); ?>

    <?php if (!empty($balanceMsg)): ?>
    <p class="lead text-bold text-center"><?= $balanceMsg ?></p>
    <?php endif; ?>
    <?= \backend\components\grid\GridView::widget([
        'dataProvider' => $dataProvider,
        'showFooter' => false,
        'footerRowOptions' => ['class' => 'text-right'],
        'columns' => [
            [
                'attribute' => 'created_at',
                'label' => 'Выдан',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            [
                'attribute' => 'createdByProfileName',
                'options' => ['width' => '200'],
            ],
            [
                'attribute' => 'operationName',
                /*
                'footer' => '<strong>Итого:</strong>',
                'footerOptions' => ['class' => 'text-right'],
                */
                'options' => ['width' => '150'],
            ],
            [
                'class' => TotalAmountColumn::class,
                'attribute' => 'amount',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\FinanceTransactions */
                    /* @var $column \yii\grid\DataColumn */

                    $sign = '';
                    $class = 'text-bold ';
                    switch ($model->operation) {
                        case FinanceTransactions::OPERATION_ВЫДАЧА_ПОДОТЧЕТ:
                            $sign = '- ';
                            $class .= 'text-danger';
                            break;
                        case FinanceTransactions::OPERATION_ВОЗВРАТ_ПОДОТЧЕТА:
                        case FinanceTransactions::OPERATION_АВАНСОВЫЙ_ОТЧЕТ:
                            $sign = '+ ';
                            $class .= 'text-success';
                            break;
                    }

                    return \yii\helpers\Html::tag('span', $sign . FinanceTransactions::getPrettyAmount($model->amount), ['class' => $class]);
                },
                'options' => ['width' => '150'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-right'],
            ],
            [
                'attribute' => 'sourceName',
                'options' => ['width' => '150'],
            ],
            'comment',
        ],
    ]); ?>

</div>
