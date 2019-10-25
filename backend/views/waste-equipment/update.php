<?php

use yii\helpers\HtmlPurifier;
use \backend\controllers\WasteEquipmentController;

/* @var $this yii\web\View */
/* @var $model common\models\WasteEquipment */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . WasteEquipmentController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = WasteEquipmentController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="waste-equipment-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
