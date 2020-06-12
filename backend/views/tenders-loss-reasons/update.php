<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\TendersLossReasonsController;

/* @var $this yii\web\View */
/* @var $model common\models\TendersLossReasons */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . TendersLossReasonsController::MAIN_MENU_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = TendersLossReasonsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="tenders-loss-reasons-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
