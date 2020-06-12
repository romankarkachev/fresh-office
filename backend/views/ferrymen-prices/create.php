<?php

/* @var $this yii\web\View */
/* @var $model common\models\FerrymenPrices */

$this->title = 'Новый комплект цен | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\FerrymenPricesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="ferrymen-prices-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
