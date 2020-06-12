<?php

use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use kartik\file\FileInput;
use backend\controllers\IncomingMailController;
use common\models\IncomingMailSearch;

/* @var $this yii\web\View */
/* @var $model common\models\IncomingMail */
/* @var $dpFiles \yii\data\ActiveDataProvider */

$typeName = $model->typeName;
$date = Yii::$app->formatter->asDate($model->created_at, 'php:d.m.Y');
$dateFilter = Yii::$app->formatter->asDate($model->created_at, 'php:Y-m-d');
$incomingNumber = '№ ' . $model->inc_num;
$this->title = $typeName . ' вх. ' . $incomingNumber . ' от ' . $date . HtmlPurifier::process(' &mdash; ' . IncomingMailController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = IncomingMailController::ROOT_BREADCRUMB;
// все элементы за эту дату
$this->params['breadcrumbs'][] = ['label' => $date, 'url' => [
    '/' . IncomingMailController::URL_ROOT_FOR_SORT_PAGING,
    (new IncomingMailSearch)->formName() => ['searchCreatedAtStart' => $dateFilter, 'searchCreatedAtEnd' => $dateFilter],
], 'title' => 'Вся корреспонденция за эту дату'];
// все элементы с данным видом корреспонденции
$this->params['breadcrumbs'][] = ['label' => $typeName, 'url' => [
    '/' . IncomingMailController::URL_ROOT_FOR_SORT_PAGING,
    (new IncomingMailSearch)->formName() => [
        'type_id' => $model->type_id,
        'searchCreatedAtStart' => $dateFilter,
        'searchCreatedAtEnd' => $dateFilter,
    ],
], 'title' => 'Вся корреспонденция данного типа за эту дату'];

$this->params['breadcrumbs'][] = $incomingNumber;
?>
<div class="incoming-mail-update">
    <?= $this->render('_form', ['model' => $model]) ?>

    <?= $this->render('_files', ['dataProvider' => $model->filesAsDataProvider]); ?>

    <?= FileInput::widget([
        'id' => 'new_files',
        'name' => 'files[]',
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'maxFileCount' => 10,
            'uploadAsync' => false,
            'uploadUrl' => Url::to(IncomingMailController::URL_UPLOAD_FILES_AS_ARRAY),
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
