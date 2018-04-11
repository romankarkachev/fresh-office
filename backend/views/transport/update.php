<?php

use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model common\models\Transport */
/* @var $dpFiles \yii\data\ActiveDataProvider */
/* @var $files array массив приаттаченных к текущий модели файлов */

$modelRepresentation = $model->brand->name . ' ' . $model->rn;

$this->title = $modelRepresentation . HtmlPurifier::process(' &mdash; Транспорт | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
$this->params['breadcrumbs'][] = ['label' => $model->ferryman->name, 'url' => ['/ferrymen/update', 'id' => $model->ferryman->id]];
$this->params['breadcrumbs'][] = ['label' => 'Транспорт', 'url' => ['/ferrymen-transport', 'TransportSearch' => ['ferryman_id' => $model->ferryman->id]]];
$this->params['breadcrumbs'][] = 'Автомобиль ' . $modelRepresentation;
?>
<div class="transport-update">
    <?= $this->render('_form', ['model' => $model, 'files' => $files]) ?>

    <?php if (Yii::$app->user->can('root')): ?>
    <?= $this->render('_files', ['dataProvider' => $dpFiles]); ?>

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

    <?php endif; ?>
</div>
<?php
if (Yii::$app->user->can('root'))
    $this->registerJs(<<<JS
$("#new_files").on("filebatchuploadsuccess", function(event, data, previewId, index) {
    $.pjax.reload({container:"#afs"});
});
JS
, \yii\web\View::POS_READY);
?>
