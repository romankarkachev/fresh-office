<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\Organizations */

$this->title = $model->name . HtmlPurifier::process(' &mdash; Организации | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Организации', 'url' => ['/organizations']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="organizations-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
