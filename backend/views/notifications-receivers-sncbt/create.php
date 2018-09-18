<?php

/* @var $this yii\web\View */
/* @var $model common\models\NotifReceiversStatesNotChangedByTime */

$this->title = 'Новый E-mail | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Получатели оповещений значительно просроченных проектов', 'url' => ['/notifications-receivers-sncbt']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="notif-receivers-states-not-changed-by-time-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
