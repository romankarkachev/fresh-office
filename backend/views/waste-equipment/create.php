<?php

/* @var $this yii\web\View */
/* @var $model common\models\WasteEquipment */

$this->title = 'Новый вид используемого оборудования | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\WasteEquipmentController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новое *';
?>
<div class="waste-equipment-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
