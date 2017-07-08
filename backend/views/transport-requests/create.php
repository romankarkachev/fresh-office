<?php

/* @var $this yii\web\View */
/* @var $model common\models\TransportRequests */

$this->title = 'Новый запрос на транспорт | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Запросы на транспорт', 'url' => ['/transport-requests']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="transport-requests-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
