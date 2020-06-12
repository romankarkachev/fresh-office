<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\ProductionShipment;
use common\models\ProductionSites;

/* @var $this yii\web\View */
/* @var $model common\models\ProductionShipment */
/* @var $form yii\bootstrap\ActiveForm */

$formName = $model->formName();
$formNameId = strtolower($formName);
$ferrymanName = 'Перевозчик не идентифицирован.';
if (!empty($model->transport_id)) {
    $ferrymanName = $model->shipmentRep;
}
?>

<div class="production-shipment-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-1">
            <?= $form->field($model, 'rn')->textInput([
                'maxlength' => true,
                'placeholder' => 'В309ВХ777',
                'title' => 'Введите госномер транспортного средства',
                'onkeyup' => 'this.value = this.value.replace(/[^а-яА-ЯёЁ0-9]/ig, "").toUpperCase()',
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'site_id')->widget(Select2::class, [
                'data' => ProductionSites::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'subject')->textInput(['maxlength' => true, 'placeholder' => 'Введите тему письма']) ?>

        </div>
        <?php if ($model->fo_project_id): ?>
        <div class="col-md-2">
            <div class="form-group">
                <label>№ проекта</label>
                <p class="form-control"><?= $model->fo_project_id ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="form-group">
        <p id="ferrymanPlainText" class="text-muted"><?= $ferrymanName ?></p>
    </div>
    <?= $form->field($model, 'comment')->textarea(['rows' => 8, 'placeholder' => 'Введите текст письма']) ?>

    <?php if ($model->isNewRecord): ?>
    <div class="form-group">
        <p>Вы можете прикрепить до <strong>100</strong> файлов (выделив их в окне выбора все единоразово).</p>
        <?= $form->field($model, 'crudeFiles[]')->fileInput(['multiple' => true]) ?>

    </div>
    <?php endif; ?>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . ProductionShipment::LABEL_ROOT, ProductionShipment::URL_ROOT_ROUTE_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if (empty($model->fo_project_id)): ?>
        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

        <?= Html::submitButton('<i class="fa fa-paper-plane" aria-hidden="true"></i> Отправить', ['class' => 'btn btn-warning btn-lg', 'name' => ProductionShipment::BUTTON_SUBMIT_SEND_NAME, 'title' => 'Отправить письмо ответственным, создать проект в CRM']) ?>
        <?php endif; ?>

        <?php endif; ?>
    </div>
    <?= $form->field($model, 'transport_id', ['template' => '{input}', 'options' => ['tag' => false]])->hiddenInput()->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
<?php
$urlIdentifyTransport = \yii\helpers\Url::to(ProductionShipment::URL_IDENTIFY_TRANSPORT_ROUTE_AS_ARRAY);

$this->registerJs(<<<JS

// Обработчик изменения госномера.
//
function rnOnChange() {
    \$ptFerryman = $("#ferrymanPlainText");
    rn = $(this).val();
    if (rn) {
        \$ptFerryman.prepend('<i id="preloader" class="fa fa-spinner fa-pulse fa-fw text-primary"></i><span class="sr-only">Подождите...</span>');
        $.get("$urlIdentifyTransport?rn=" + rn, function(response) {
            if (response != false) {
                \$ptFerryman.text(response.transportRep);
                $("#$formNameId-transport_id").val(response.transport_id);
            }
        }).always(function() {
            $("#preloader").remove();
        });
    }
} // rnOnChange()

$(document).on("change", "#$formNameId-rn", rnOnChange);
JS
, \yii\web\View::POS_READY);
?>
