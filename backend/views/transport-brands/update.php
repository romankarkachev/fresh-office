<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\TransportBrands */

$this->title = $model->name . HtmlPurifier::process(' &mdash; Марки автомобилей | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Марки автомобилей', 'url' => ['/transport-brands']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="transport-brands-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
