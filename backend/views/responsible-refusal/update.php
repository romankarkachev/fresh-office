<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\ResponsibleRefusal */

$this->title = $model->responsible_name . HtmlPurifier::process(' &mdash; Ответственные лица (отказ) | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Ответственные лица (отказ)', 'url' => ['/responsible-refusal']];
$this->params['breadcrumbs'][] = $model->responsible_name;
?>
<div class="responsible-refusal-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
