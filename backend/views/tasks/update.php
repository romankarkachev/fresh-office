<?php

use kartik\file\FileInput;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use backend\controllers\TasksController;

/* @var $this yii\web\View */
/* @var $model common\models\Tasks */
/* @var $dpFiles \yii\data\ActiveDataProvider */

$modelRep = '#' . $model->id . (!empty($model->purpose) ? ' &mdash; ' . $model->purpose : '');
$this->title = HtmlPurifier::process($modelRep . ' &mdash; ' . TasksController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = TasksController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $modelRep;
?>
<div class="tasks-update">
    <?= $this->render('_form', ['model' => $model]) ?>

    <?= $this->render('_files', ['dataProvider' => $dpFiles]); ?>

    <?= FileInput::widget([
        'id' => 'new_files',
        'name' => 'files[]',
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'maxFileCount' => 10,
            'uploadAsync' => false,
            'uploadUrl' => Url::to(TasksController::URL_UPLOAD_FILES_AS_ARRAY),
            'uploadExtraData' => [
                'obj_id' => $model->id,
            ],
        ]
    ]) ?>

</div>
