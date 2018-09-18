<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use common\models\foProjects;
use common\models\foProjectsStates;
use common\models\NotifReceiversStatesNotChangedByTime;

/* @var $this yii\web\View */
/* @var $model common\models\NotifReceiversStatesNotChangedByTime */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $time integer время в оригинальной записи в секундах */
?>

<div class="notif-receivers-states-not-changed-by-time-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'receiver')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Введите наименование']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'state_id')->widget(Select2::className(), [
                'data' => foProjectsStates::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'time')->widget(MaskedInput::className(), [
                'clientOptions' => ['alias' =>  'numeric'],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => '0',
            ]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'periodicity')->widget(Select2::className(), [
                'data' => NotifReceiversStatesNotChangedByTime::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <?php if (!$model->isNewRecord): ?>
    <p>Сохраненное значение: <?= foProjects::downcounter($time); ?>.</p>
    <?php endif; ?>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Получатели оповещений', ['/notifications-receivers-sncbt'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
