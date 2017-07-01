<?php

use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model common\models\Ferrymen */
/* @var $dpFiles \yii\data\ActiveDataProvider */
/* @var $dpDrivers common\models\Drivers[] */
/* @var $dpTransport common\models\Transport[] */

$this->title = $model->name . HtmlPurifier::process(' &mdash; Перевозчики | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="ferrymen-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

    <?php if (!$model->isNewRecord): ?>
    <div class="row">
        <div class="col-md-5">
            <?= $this->render('_drivers', [
                'model' => $model,
                'dpDrivers' => $dpDrivers
            ]) ?>

        </div>
        <div class="col-md-7">
            <?= $this->render('_transport', [
                'model' => $model,
                'dpTransport' => $dpTransport
            ]) ?>

        </div>
    </div>
    <div class="page-header"><h3>Файлы</h3></div>
    <?= $this->render('_files', ['dataProvider' => $dpFiles]); ?>

    <?= FileInput::widget([
        'id' => 'new_files',
        'name' => 'files[]',
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'maxFileCount' => 10,
            'uploadAsync' => false,
            'uploadUrl' => Url::to(['/ferrymen/upload-files']),
            'uploadExtraData' => [
                'obj_id' => $model->id,
            ],
        ]
    ]) ?>

    <?php endif; ?>
</div>
<?php
$this->registerJs(<<<JS
$("#new_files").on("filebatchuploadsuccess", function(event, data, previewId, index) {
    $.pjax.reload({container:"#afs"});
});
JS
, \yii\web\View::POS_READY);
?>
