<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\UploadingFilesMeanings */

$this->title = $model->name . HtmlPurifier::process(' &mdash; Типы контента подгружаемых файлов | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Типы контента подгружаемых файлов', 'url' => ['/uploading-files-meanings']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="uploading-files-meanings-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
