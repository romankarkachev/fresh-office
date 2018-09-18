<?php

/* @var $this yii\web\View */
/* @var $model common\models\FileStorage */

$this->title = 'Новый файл | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Файловое хранилище', 'url' => ['/storage']];
if (isset($bcCa)) $this->params['breadcrumbs'][] = $bcCa;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="file-storage-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
