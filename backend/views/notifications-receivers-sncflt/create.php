<?php

/* @var $this yii\web\View */
/* @var $model common\models\NotifReceiversStatesNotChangedForALongTime */

$this->title = 'Новый E-mail | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Получатели уведомлений о просроченных сегодня проектах', 'url' => ['/notifications-receivers-sncflt']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="notif-receivers-states-not-changed-for-along-time-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
