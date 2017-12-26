<?php

use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use kartik\file\FileInput;

/* @var $this yii\web\View */
/* @var $model common\models\CorrespondencePackages */
/* @var $dpFiles \yii\data\ActiveDataProvider */

$modelRep = 'Пакет № ' . $model->id . ' (создан ' . Yii::$app->formatter->asDate($model->created_at, 'php:d.m.Y в H:i') . ')';

$this->title = $modelRep . ' проект # ' . $model->fo_project_id . ', контрагент ' . $model->customer_name . HtmlPurifier::process(' &mdash; Пакеты корреспонденции | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Пакеты корреспонденции', 'url' => ['/correspondence-packages']];
$this->params['breadcrumbs'][] = $modelRep;
?>
<div class="correspondence-packages-update">
    <?= $this->render('_form', ['model' => $model]) ?>

    <?= $this->render('_files', ['dataProvider' => $dpFiles]); ?>

    <?= FileInput::widget([
        'id' => 'new_files',
        'name' => 'files[]',
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'maxFileCount' => 10,
            'uploadAsync' => false,
            'uploadUrl' => Url::to(['/correspondence-packages/upload-files']),
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
