<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\Drivers */

if ($model->ferryman != null) {
    $this->title = 'Новый водитель перевозчика ' . $model->ferryman->name . HtmlPurifier::process(' &mdash; Водители | ') . Yii::$app->name;
    $this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
    $this->params['breadcrumbs'][] = ['label' => $model->ferryman->name, 'url' => ['/ferrymen/update', 'id' => $model->ferryman->id]];
    $this->params['breadcrumbs'][] = 'Новый водитель *';
}
else {
    $this->title = 'Новый водитель | ' . Yii::$app->name;
    $this->params['breadcrumbs'][] = ['label' => 'Водители', 'url' => ['/ferrymen-drivers']];
    $this->params['breadcrumbs'][] = 'Новый *';
}
?>
<div class="drivers-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
