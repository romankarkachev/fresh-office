<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\FileStorage */

$this->title = $model->ofn . HtmlPurifier::process(' &mdash; Файловое хранилище | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Файловое хранилище', 'url' => ['/storage']];
$this->params['breadcrumbs'][] = $model->ofn;
?>
<div class="file-storage-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
