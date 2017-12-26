<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\PaymentOrders */
/* @var $dpFiles \yii\data\ActiveDataProvider */

$this->title = $model->modelRep . HtmlPurifier::process(' &mdash; Платежные ордеры | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Платежные ордеры', 'url' => ['/payment-orders']];
$this->params['breadcrumbs'][] = $model->modelRep;
?>
<div class="payment-orders-update">
    <?= $this->render('_form_readonly', ['model' => $model]) ?>

    <?= $this->render('_files', ['dataProvider' => $dpFiles]); ?>

</div>
