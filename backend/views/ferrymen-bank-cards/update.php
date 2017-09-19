<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\FerrymenBankDetails */
/* @var $dpFiles \yii\data\ActiveDataProvider */

$modelRepresentation = $model->name_full . ', номер счета ' . $model->bank_an . ' в ' . $model->bank_name;

$this->title = $modelRepresentation . HtmlPurifier::process(' &mdash; Банковские счета | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
$this->params['breadcrumbs'][] = ['label' => $model->ferryman->name, 'url' => ['/ferrymen/update', 'id' => $model->ferryman->id]];
//$this->params['breadcrumbs'][] = ['label' => 'Банковские счета', 'url' => ['/ferrymen-drivers', 'DriversSearch' => ['ferryman_id' => $model->ferryman->id]]];
$this->params['breadcrumbs'][] = 'Банковские счета';
$this->params['breadcrumbs'][] = $modelRepresentation;
?>
<div class="ferrymen-bank-details-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
