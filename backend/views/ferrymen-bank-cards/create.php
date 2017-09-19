<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\FerrymenBankCards */

if ($model->ferryman != null) {
    $this->title = 'Новая банковская карта перевозчика ' . $model->ferryman->name . HtmlPurifier::process(' &mdash; Банковские карты | ') . Yii::$app->name;
    $this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
    $this->params['breadcrumbs'][] = ['label' => $model->ferryman->name, 'url' => ['/ferrymen/update', 'id' => $model->ferryman->id]];
    $this->params['breadcrumbs'][] = 'Новая банковская карта *';
}
else {
    $this->title = 'Новая банковская карта | ' . Yii::$app->name;
    $this->params['breadcrumbs'][] = ['label' => 'Банковские карты', 'url' => ['/ferrymen-bank-cards']];
    $this->params['breadcrumbs'][] = 'Новый *';
}
?>
<div class="bank-cards-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
