<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\NotifReceiversStatesNotChangedForALongTime */

$this->title = $model->receiver . HtmlPurifier::process(' &mdash; Получатели уведомлений о просроченных сегодня проектах | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Получатели уведомлений о просроченных сегодня проектах', 'url' => ['/notifications-receivers-sncflt']];
$this->params['breadcrumbs'][] = $model->receiver;
?>
<div class="notif-receivers-states-not-changed-for-along-time-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
