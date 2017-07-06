<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\PackingTypes */

$this->title = $model->name . HtmlPurifier::process(' &mdash; Виды упаковки | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Виды упаковки', 'url' => ['/packing-types']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="packing-types-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
