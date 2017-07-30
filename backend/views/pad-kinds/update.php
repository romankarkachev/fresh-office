<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\PadKinds */

$this->title = $model->name . HtmlPurifier::process(' &mdash; Виды документов | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Виды документов', 'url' => ['/pad-kinds']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="pad-kinds-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
