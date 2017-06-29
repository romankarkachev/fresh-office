<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\Transport */

if ($model->ferryman != null) {
    $this->title = 'Новый автомобиль перевозчика ' . $model->ferryman->name . HtmlPurifier::process(' &mdash; Транспорт | ') . Yii::$app->name;
    $this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
    $this->params['breadcrumbs'][] = ['label' => $model->ferryman->name, 'url' => ['/ferrymen/update', 'id' => $model->ferryman->id]];
    $this->params['breadcrumbs'][] = 'Новый автомобиль *';
}
else {
    $this->title = 'Новый автомобиль | ' . Yii::$app->name;
    $this->params['breadcrumbs'][] = ['label' => 'Транспорт', 'url' => ['/ferrymen-transport']];
    $this->params['breadcrumbs'][] = 'Новый *';
}
?>
<div class="transport-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
