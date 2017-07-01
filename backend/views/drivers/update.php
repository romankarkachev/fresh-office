<?php

use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model common\models\Drivers */
/* @var $dpFiles \yii\data\ActiveDataProvider */

$modelRepresentation = $model->surname . ' ' . $model->name . ' ' . $model->patronymic;

$this->title = $modelRepresentation . HtmlPurifier::process(' &mdash; Водители | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
$this->params['breadcrumbs'][] = ['label' => $model->ferryman->name, 'url' => ['/ferrymen/update', 'id' => $model->ferryman->id]];
$this->params['breadcrumbs'][] = ['label' => 'Водители', 'url' => ['/ferrymen-drivers', 'DriversSearch' => ['ferryman_id' => $model->ferryman->id]]];
$this->params['breadcrumbs'][] = $modelRepresentation;
?>
<div class="drivers-update">
    <?= $this->render('_form', ['model' => $model]) ?>

    <?= $this->render('_files', ['dataProvider' => $dpFiles]); ?>

    <?= FileInput::widget([
        'id' => 'new_files',
        'name' => 'files[]',
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'maxFileCount' => 10,
            'uploadAsync' => false,
            'uploadUrl' => Url::to(['/ferrymen-drivers/upload-files']),
            'uploadExtraData' => [
                'obj_id' => $model->id,
            ],
        ]
    ]) ?>

</div>
<?php
$this->registerJs(<<<JS
$("#new_files").on("filebatchuploadsuccess", function(event, data, previewId, index) {
    $.pjax.reload({container:"#afs"});
});
JS
, \yii\web\View::POS_READY);
?>
