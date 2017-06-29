<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\Ferrymen */
/* @var $dpDrivers common\models\Drivers[] */
/* @var $dpTransport common\models\Transport[] */

$this->title = $model->name . HtmlPurifier::process(' &mdash; Перевозчики | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="ferrymen-update">
    <?= $this->render('_form', [
        'model' => $model,
        'dpDrivers' => $dpDrivers,
        'dpTransport' => $dpTransport,
    ]) ?>

    <?php if (!$model->isNewRecord): ?>
    <div class="row">
        <div class="col-md-5">
            <?= $this->render('_drivers', [
                'model' => $model,
                'dpDrivers' => $dpDrivers
            ]) ?>

        </div>
        <div class="col-md-7">
            <?= $this->render('_transport', [
                'model' => $model,
                'dpTransport' => $dpTransport
            ]) ?>

        </div>
    </div>
    <?php endif; ?>
</div>
