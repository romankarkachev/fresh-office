<?php

use common\models\ProductionShipment;

/* @var $this yii\web\View */
/* @var $model common\models\ProductionShipment */

$this->title = 'Новая отправка | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => ProductionShipment::LABEL_ROOT, 'url' => ProductionShipment::URL_ROOT_ROUTE_AS_ARRAY];
$this->params['breadcrumbs'][] = 'Новая *';
?>
<div class="production-shipment-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
