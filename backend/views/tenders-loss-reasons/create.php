<?php

/* @var $this yii\web\View */
/* @var $model common\models\TendersLossReasons */

$this->title = 'Новая возможная причина проигрыша в конкурсе | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\TendersLossReasonsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новая *';
?>
<div class="tenders-loss-reasons-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
