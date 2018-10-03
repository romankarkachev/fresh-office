<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use kartik\file\FileInput;
use backend\controllers\EcoProjectsController;

/* @var $this yii\web\View */
/* @var $model common\models\EcoProjectsMilestones */
/* @var $dpFiles \yii\data\ActiveDataProvider */

$this->title = 'Этап ' . $model->order_no . ': ' . $model->milestoneName . ' проекта № ' . $model->project_id . HtmlPurifier::process(' &mdash; ' . EcoProjectsController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = EcoProjectsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = ['label' => 'Проект № ' . $model->project_id, 'url' => ['/' . EcoProjectsController::ROOT_URL_FOR_SORT_PAGING . '/update', 'id' => $model->project_id]];
$this->params['breadcrumbs'][] = 'Файлы к этапу № ' . $model->order_no;
?>

<div class="eco-projects-milestones-form">
    <p class="lead"><?= $model->milestoneName ?></p>
    <p>Плановая дата завершения: <?= Yii::$app->formatter->asDate(strtotime($model->date_close_plan . ' 00:00:00'), 'php:d F Y г.') ?></p>
    <?php if (empty($model->closed_at)): ?>
    <?php $form = ActiveForm::begin([
        'id' => 'frmCloseMilestone',
    ]); ?>

    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>

    <?php if ($model->project->currentMilestone->id == $model->id): ?>
    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-flag-checkered" aria-hidden="true"></i> Завершить этап', ['class' => 'btn btn-success btn-lg', 'name' => 'close_milestone', 'title' => 'Завершить этап немедленно']) ?>

    </div>
    <?php else: ?>
    <div class="alert alert-danger">Очередь этого этапа еще не наступила! Необходимо закрыть предыдущие этапы, чтобы появилась возможность закрыть этот.</div>
    <?php endif; ?>
    <?php ActiveForm::end(); ?>

    <?php if ($model->is_file_reqiured): ?>
    <?php Pjax::begin(['id' => 'pjax-fileinput']); ?>

    <?= FileInput::widget([
        'id' => 'new_files',
        'name' => 'files[]',
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'maxFileCount' => 10,
            'uploadAsync' => false,
            'uploadUrl' => \yii\helpers\Url::to(['/' . \backend\controllers\EcoProjectsController::ROOT_URL_FOR_SORT_PAGING . '/milestone-upload-files']),
            'uploadExtraData' => [
                'obj_id' => $model->id,
            ],
        ]
    ]) ?>

    <?php Pjax::end(); ?>

    <?php endif; ?>
    <?php else: ?>
    <p>Фактическая дата завершения: <?= Yii::$app->formatter->asDate($model->closed_at, 'php:d F Y г. в H:i') ?>.</p>
    <?php endif; ?>
    <?php if ($model->is_file_reqiured): ?>
    <?= $this->render('_files', ['model' => $model, 'dataProvider' => $dpFiles]); ?>
    <?php endif; ?>
</div>
