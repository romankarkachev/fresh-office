<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\Drivers */
/* @var $files array массив приаттаченных к текущий модели файлов */

$modelRepresentation = $model->surname . ' ' . $model->name . ' ' . $model->patronymic;

$this->title = $modelRepresentation . HtmlPurifier::process(' &mdash; Водители | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Водители', 'url' => ['/drivers']];
$this->params['breadcrumbs'][] = $modelRepresentation;
?>
<div class="drivers-update">
    <?= $this->render('_form', ['model' => $model, 'files' => $files]) ?>

</div>
