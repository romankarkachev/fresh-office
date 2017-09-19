<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\FerrymenBankDetails */

if ($model->ferryman != null) {
    $this->title = 'Новый банковский счет перевозчика ' . $model->ferryman->name . HtmlPurifier::process(' &mdash; Банковские счета | ') . Yii::$app->name;
    $this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
    $this->params['breadcrumbs'][] = ['label' => $model->ferryman->name, 'url' => ['/ferrymen/update', 'id' => $model->ferryman->id]];
    $this->params['breadcrumbs'][] = 'Новый банковский счет *';
}
else {
    $this->title = 'Новый банковский счет | ' . Yii::$app->name;
    $this->params['breadcrumbs'][] = ['label' => 'Банковские счета', 'url' => ['/ferrymen-bank-details']];
    $this->params['breadcrumbs'][] = 'Новый *';
}
?>
<div class="bank-details-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
