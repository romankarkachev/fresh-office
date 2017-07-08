<?php

use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model common\models\TransportRequests */
/* @var $waste common\models\TransportRequestsWaste[] */
/* @var $transport common\models\TransportRequestsTransport[] */
/* @var $dpFiles \yii\data\ActiveDataProvider */

$this->title = '№ ' . $model->id . ' от ' . Yii::$app->formatter->asDate($model->created_at, 'php:d.m.Y') . HtmlPurifier::process(' &mdash; Запросы на транспорт | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Запросы на транспорт', 'url' => ['/transport-requests']];
$this->params['breadcrumbs'][] = $model->representation . ' (автор: ' . $model->createdByName . ')';
?>
<div class="transport-requests-update">
    <?= $this->render('_form', [
        'model' => $model,
        'waste' => $waste,
        'transport' => $transport,
    ]) ?>

    <div class="page-header"><h3>Файлы</h3></div>
    <?= $this->render('_files', ['dataProvider' => $dpFiles]); ?>

    <?= FileInput::widget([
        'id' => 'new_files',
        'name' => 'files[]',
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'maxFileCount' => 10,
            'uploadAsync' => false,
            'uploadUrl' => Url::to(['/transport-requests/upload-files']),
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
