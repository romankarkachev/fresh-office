<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\FerrymenBankCards */

$modelRepresentation = $model->cardholder . ', номер карты ' . $model->number . ($model->bank != null ? ' в банке ' . $model->bank: '');

$this->title = $modelRepresentation . HtmlPurifier::process(' &mdash; Банковские карты | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Банковские карты', 'url' => ['/bank-cards']];
$this->params['breadcrumbs'][] = $modelRepresentation;
?>
<div class="bank-cards-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
