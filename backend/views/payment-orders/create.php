<?php

/* @var $this yii\web\View */
/* @var $model common\models\PaymentOrders */

$this->title = 'Новый платежный ордер | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Платежные ордеры', 'url' => ['/payment-orders']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="payment-orders-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
