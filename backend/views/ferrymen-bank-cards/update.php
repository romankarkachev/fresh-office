<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\FerrymenBankCards */
/* @var $dpFiles \yii\data\ActiveDataProvider */

$modelRepresentation = $model->cardholder . ', номер карты ' . $model->number . ($model->bank != null ? ' в банке ' . $model->bank: '');

$this->title = $modelRepresentation . HtmlPurifier::process(' &mdash; Банковские карты | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
$this->params['breadcrumbs'][] = ['label' => $model->ferryman->name, 'url' => ['/ferrymen/update', 'id' => $model->ferryman->id]];
//$this->params['breadcrumbs'][] = ['label' => 'Банковские карты', 'url' => ['/ferrymen-bank-cards', 'FerrymenBankCardsSearch' => ['ferryman_id' => $model->ferryman->id]]];
$this->params['breadcrumbs'][] = 'Банковские карты';
$this->params['breadcrumbs'][] = $modelRepresentation;
?>
<div class="ferrymen-bank-cards-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
