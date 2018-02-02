<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\FileStorage */

$this->title = $model->ofn . HtmlPurifier::process(' &mdash; Файловое хранилище | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Файловое хранилище', 'url' => ['/storage']];
$this->params['breadcrumbs'][] = ['label' => $model->ca_name, 'url' => ['/storage', 'FileStorageSearch' => ['ca_id' => $model->ca_id]]];
$this->params['breadcrumbs'][] = $model->ofn;

$fileUrl = 'http://' . Yii::$app->params['serverIp'] . '/storage/download-from-outside?id='.$model->id;
?>
<div class="file-storage-preview">
    <iframe src="http://docs.google.com/gview?url=<?= $fileUrl; ?>&embedded=true" style="width:100%; height:800px;" frameborder="0"></iframe>

</div>
