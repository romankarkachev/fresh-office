<?php

use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model common\models\Transport */
/* @var $files \yii\data\ActiveDataProvider */

$modelRepresentation = $model->brand->name . ' ' . $model->rn;

$this->title = $modelRepresentation . HtmlPurifier::process(' &mdash; Транспорт | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
$this->params['breadcrumbs'][] = ['label' => $model->ferryman->name, 'url' => ['/ferrymen/update', 'id' => $model->ferryman->id]];
$this->params['breadcrumbs'][] = ['label' => 'Транспорт', 'url' => ['/ferrymen-transport', 'TransportSearch' => ['ferryman_id' => $model->ferryman->id]]];
$this->params['breadcrumbs'][] = 'Автомобиль ' . $modelRepresentation;
?>
<div class="transport-update">
    <?= $this->render('_form', ['model' => $model]) ?>

    <?= $this->render('_files', ['dataProvider' => $files]); ?>

    <?= FileInput::widget([
        'id' => 'new_files',
        'name' => 'files[]',
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'maxFileCount' => 10,
            'uploadAsync' => false,
            'uploadUrl' => Url::to(['/ferrymen-transport/upload-files']),
            'uploadExtraData' => [
                'obj_id' => $model->id,
            ],
        ]
    ]) ?>
</div>
