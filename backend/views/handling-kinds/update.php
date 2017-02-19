<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\HandlingKinds */

$this->title = $model->name . HtmlPurifier::process(' &mdash; Виды обращения | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Виды обращения', 'url' => ['/handling-kinds']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="handling-kinds-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
