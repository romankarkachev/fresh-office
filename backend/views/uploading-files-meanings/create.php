<?php

/* @var $this yii\web\View */
/* @var $model common\models\UploadingFilesMeanings */

$this->title = 'Новый тип контента | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Типы контента подгружаемых файлов', 'url' => ['/uploading-files-meanings']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="uploading-files-meanings-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
