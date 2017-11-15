<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\FileStorage */

$this->title = $model->typeName . ' ' . $model->ofn . HtmlPurifier::process(' &mdash; Файловое хранилище | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Файловое хранилище', 'url' => ['/storage']];
$this->params['breadcrumbs'][] = $model->ofn;
?>
<div class="file-storage-view">
    <p>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'uploaded_at',
            'uploadedByProfileName',
            'typeName',
            'ofn',
            'size',
        ],
    ]) ?>

</div>
