<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\TransportTypes */

$this->title = $model->name . HtmlPurifier::process(' &mdash; Типы техники | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Типы техники', 'url' => ['/transport-types']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="transport-types-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
