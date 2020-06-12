<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use common\models\foProjects;
use common\models\NotifReceiversStatesNotChangedByTime;

/* @var $this yii\web\View */
/* @var $model common\models\NotifReceiversStatesNotChangedByTime */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $time integer время в оригинальной записи в секундах */
/* @var $states array статусы, которые доступны пользователю в зависимости от раздела учета */

$formName = strtolower($model->formName());
?>

<div class="notif-receivers-states-not-changed-by-time-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'section')->widget(Select2::className(), [
                'data' => NotifReceiversStatesNotChangedByTime::arrayMapOfSectionsForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'receiver')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Введите наименование']) ?>

        </div>
        <div id="block-state" class="col-md-2"><?= $this->render('_field_state', ['model' => $model, 'form' => $form, 'states' => $states]); ?></div>
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
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Получатели оповещений', ['/notifications-receivers-sncbt'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$urlSectionOnChange = \yii\helpers\Url::to(\backend\controllers\NotificationsReceiversSncbtController::URL_RENDER_STATE_BLOCK_AS_ARRAY);

$this->registerJs(<<<JS

// Обработчик изменения значения в поле "Раздел учета".
//
function sectionOnChange() {
    section = $("#$formName-section").val();
    if (section) {
        \$block = $("#block-state");
        \$block.html('<p class="text-center"><i class="fa fa-spinner fa-pulse fa-fw text-primary text-muted"></i><span class="sr-only">Подождите...</span></p>');
        \$block.load("$urlSectionOnChange?section=" + section);
    }
} // sectionOnChange()

$(document).on("change", "#$formName-section", sectionOnChange);
JS
, \yii\web\View::POS_READY);
?>
