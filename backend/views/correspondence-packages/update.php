<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\CorrespondencePackages */

$modelRep = 'Пакет № ' . $model->id . ' (создан ' . Yii::$app->formatter->asDate($model->created_at, 'php:d.m.Y в H:i') . ')';

$this->title = $modelRep . ' проект # ' . $model->fo_project_id . ', контрагент ' . $model->customer_name . HtmlPurifier::process(' &mdash; Пакеты корреспонденции | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Пакеты корреспонденции', 'url' => ['/correspondence-packages']];
$this->params['breadcrumbs'][] = $modelRep;
?>
<div class="correspondence-packages-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
