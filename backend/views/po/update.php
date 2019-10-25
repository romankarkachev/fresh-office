<?php

use backend\controllers\PoController;
use kartik\file\FileInput;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\Po */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $propertiesBlock string блок со свойствами в html-формате */
/* @var $dpFiles \yii\data\ActiveDataProvider */
/* @var $dpProperties \yii\data\ActiveDataProvider */

$this->title = $model->id . HtmlPurifier::process(' &mdash; ' . PoController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = PoController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->modelRep;

$paymentDetails = ''; // возможно, здесь будут условия платежа (например, реквизиты банковского счета)
?>
<div class="po-update">
    <?= $this->render('_form', ['model' => $model, 'dpProperties' => $dpProperties]) ?>

    <?= $this->render('_files', ['dataProvider' => $dpFiles]); ?>

    <?= FileInput::widget([
        'id' => 'new_files',
        'name' => 'files[]',
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'maxFileCount' => 10,
            'uploadAsync' => false,
            'uploadUrl' => Url::to(PoController::URL_UPLOAD_FILES_AS_ARRAY),
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
