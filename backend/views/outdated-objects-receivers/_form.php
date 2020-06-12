<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use common\models\OutdatedObjectsReceivers;
use common\models\NotifReceiversStatesNotChangedByTime;
use common\models\foProjects;
use backend\controllers\OutdatedObjectsReceiversController;

/* @var $this yii\web\View */
/* @var $model common\models\OutdatedObjectsReceivers */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $time integer время в оригинальной записи в секундах */
?>

<div class="outdated-objects-receivers-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'section')->widget(Select2::class, [
                'data' => OutdatedObjectsReceivers::arrayMapOfSectionsForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'receiver')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Введите наименование']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'time')->widget(MaskedInput::class, [
                'clientOptions' => ['alias' =>  'numeric'],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => '0',
            ]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'periodicity')->widget(Select2::class, [
                'data' => NotifReceiversStatesNotChangedByTime::arrayMapOfPeriodicityUnitsForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <?php if (!$model->isNewRecord): ?>
    <p>Сохраненное значение: <?= trim(foProjects::downcounter($time)); ?>.</p>
    <?php endif; ?>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . OutdatedObjectsReceiversController::MAIN_MENU_LABEL, OutdatedObjectsReceiversController::URL_ROOT_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
