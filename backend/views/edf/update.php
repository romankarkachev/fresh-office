<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\EdfController;
use common\models\EdfDialogs;

/* @var $this yii\web\View */
/* @var $model common\models\Edf */
/* @var $tp common\models\EdfTp[]|\yii\data\ActiveDataProvider */
/* @var $hasAccess bool наличие доступа к нескольким объектам электронного документа (менеджер не имеет) */
/* @var $canEditManager bool возможность изменить ответственного */
/* @var $dpDialogs \yii\data\ActiveDataProvider */
/* @var $dpFiles \yii\data\ActiveDataProvider */
/* @var $dpHistory \yii\data\ActiveDataProvider */

$modelRep = $model->typeName . ' № ' . $model->doc_num . ' от ' . Yii::$app->formatter->asDate($model->doc_date . ' 00:00:00', 'php:d.m.Y г.') . ' (' . $model->stateName . ')';
$this->title = $modelRep . HtmlPurifier::process(' &mdash; ' . EdfController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = EdfController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $modelRep;
?>
<div class="edf-update">
    <?= $this->render('_form', ['model' => $model, 'tp' => $tp, 'hasAccess' => $hasAccess, 'canEditManager' => $canEditManager]) ?>

    <?= $this->render('_history', ['dataProvider' => $dpHistory]); ?>

    <?= $this->render('_dialogs', [
        'dataProvider' => $dpDialogs,
        'model' => new EdfDialogs([
            'ed_id' => $model->id,
            'created_by' => Yii::$app->user->id,
        ]),
        'action' => EdfController::ADD_DIALOG_MESSAGE_URL,
    ]); ?>

    <?= $this->render('_files', ['model' => $model, 'dataProvider' => $dpFiles]); ?>

    <?= \kartik\file\FileInput::widget([
        'id' => 'new_files',
        'name' => 'files[]',
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'maxFileCount' => 10,
            'uploadAsync' => false,
            'uploadUrl' => \yii\helpers\Url::to(EdfController::UPLOAD_FILES_URL_AS_ARRAY),
            'uploadExtraData' => [
                'obj_id' => $model->id,
            ],
        ]
    ]) ?>

</div>
<?php
$this->registerJs(<<<JS
$("#new_files").on("fileuploaded filebatchuploadsuccess", function(event, data, previewId, index) {
    $.pjax.reload({container:"#pjax-files"});
});
JS
, \yii\web\View::POS_READY);
?>
