<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\NotifReceiversStatesNotChangedByTime */
/* @var $time integer время в оригинальной записи в секундах */
/* @var $states array статусы, которые доступны пользователю в зависимости от раздела учета */

$this->title = $model->receiver . HtmlPurifier::process(' &mdash; Получатели оповещений значительно просроченных проектов | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Получатели оповещений значительно просроченных проектов', 'url' => ['/notifications-receivers-sncbt']];
$this->params['breadcrumbs'][] = $model->receiver;
?>
<div class="notif-receivers-states-not-changed-by-time-update">
    <?= $this->render('_form', [
        'model' => $model,
        'time' => $time,
        'states' => $states,
    ]) ?>

</div>
