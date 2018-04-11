<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\Transport */
/* @var $files array массив приаттаченных к текущий модели файлов */

$modelRepresentation = $model->brand->name . ' ' . $model->rn;

$this->title = $modelRepresentation . HtmlPurifier::process(' &mdash; Транспорт | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Транспорт', 'url' => ['/transport']];
$this->params['breadcrumbs'][] = 'Автомобиль ' . $modelRepresentation;
?>
<div class="transport-update">
    <?= $this->render('_form', ['model' => $model, 'files' => $files]) ?>

</div>
