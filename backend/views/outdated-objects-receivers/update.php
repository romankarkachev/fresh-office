<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\OutdatedObjectsReceivers */
/* @var $time integer время в оригинальной записи в секундах */

$this->title = $model->receiver . HtmlPurifier::process(' &mdash; Получатели оповещений по просроченным объектам | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\OutdatedObjectsReceiversController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->receiver;
?>
<div class="outdated-objects-receivers-update">
    <?= $this->render('_form', ['model' => $model, 'time' => $time]) ?>

</div>
