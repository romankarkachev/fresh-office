<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\Units */

$this->title = $model->name . HtmlPurifier::process(' &mdash; Единицы измерения | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Единицы измерения', 'url' => ['/units']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="units-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
