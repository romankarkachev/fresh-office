<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\FerrymenPricesController;

/* @var $this yii\web\View */
/* @var $model common\models\FerrymenPrices */

$this->title = $model->ferrymanName . HtmlPurifier::process(' &mdash; ' . FerrymenPricesController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = FerrymenPricesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->ferrymanName;
?>
<div class="ferrymen-prices-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
