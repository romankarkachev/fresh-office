<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\FerrymenBankDetails */

$modelRepresentation = 'Р/с ' . $model->bank_an . ' в ' . $model->bank_name;

$this->title = $modelRepresentation . HtmlPurifier::process(' &mdash; Банковские счета | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Банковские счета', 'url' => ['/bank-accounts']];
$this->params['breadcrumbs'][] = $modelRepresentation;
?>
<div class="bank-accounts-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
