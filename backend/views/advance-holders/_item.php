<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $item \common\models\FinanceAdvanceHolders */

$urlTransactions = \yii\helpers\ArrayHelper::merge(\backend\controllers\AdvanceHoldersController::URL_TRANSACTIONS_AS_ARRAY, ['id' => $item->user_id]);
$h2Options = ['class' => 'text-bold', 'style' => 'margin-top: 0px; margin-left:0px;'];
$balance = \common\models\FinanceTransactions::getPrettyAmount($item->balance, 'fontawesome');
if ($item->balance < 0) {
    $h2Options['style'] .= ' color:#ec1a15;';
    $h2Options['title'] = 'Задолженность перед сотрудником';
    $balance = '- ' . $balance;
}
else {
    $h2Options['style'] .= ' color:#38a23a;';
    $h2Options['title'] = 'Задолженность сотрудника';
}
?>

    <div class="panel panel-info">
        <div class="panel-heading"><h4 class="panel-title"><?= Html::a($item->userProfileName . ' &rarr;', $urlTransactions, ['title' => 'Показать все финансовые движения по этому сотруднику']) ?></h4></div>
        <div class="panel-body text-right"><?= Html::tag('h2', $balance, $h2Options) ?></div>
        <div class="panel-footer">
            <small class="text-muted" title="Информация о последней операции"><?= $item->lastTransactionRep ?></small>
        </div>
    </div>
