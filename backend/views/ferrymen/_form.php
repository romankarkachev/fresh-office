<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\Ferrymen;
use common\models\FerrymenTypes;
use common\models\PaymentConditions;
use common\models\Opfh;

/* @var $this yii\web\View */
/* @var $model common\models\Ferrymen */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dpDrivers common\models\Drivers[] */
/* @var $dpTransport common\models\Transport[] */

$labelAtiCode = $model->attributeLabels()['ati_code'];
?>

<div class="ferrymen-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Введите наименование']) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'name_crm')->textInput(['maxlength' => true, 'placeholder' => 'Введите наименование из CRM']) ?>

        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'notify_when_payment_orders_created')->checkbox()->label('Отправлять уведомление при импорте ордеров', [
                'title' => 'Необходимость отправлять уведомление перевозчику при импорте платежного ордера на него',
                'style' => 'padding-left: 0px;'
            ]) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'state_id')->widget(Select2::className(), [
                'data' => Ferrymen::arrayMapOfStatesForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'ft_id')->widget(Select2::className(), [
                'data' => FerrymenTypes::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'pc_id')->widget(Select2::className(), [
                'data' => PaymentConditions::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'opfh_id')->widget(Select2::className(), [
                'data' => Opfh::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'tax_kind')->widget(Select2::className(), [
                'data' => Ferrymen::arrayMapOfTaxKindsForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'ati_code')->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '999999999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['placeholder' => 'Введите код АТИ'])->label(null, ['id' => 'label-ati_code']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12"><p class="lead">Диспетчер</p></div>
        <div class="col-md-2">
            <?= $form->field($model, 'contact_person')->textInput(['maxlength' => true, 'placeholder' => 'Введите имя', 'title' => 'Введите имя контактного лица']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'phone')->textInput(['maxlength' => true, 'placeholder' => 'Введите телефоны']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'Введите E-mail']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'post')->textInput(['maxlength' => true, 'placeholder' => 'Введите должность']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12"><p class="lead">Руководитель</p></div>
        <div class="col-md-2">
            <?= $form->field($model, 'contact_person_dir')->textInput(['maxlength' => true, 'placeholder' => 'Введите имя', 'title' => 'Введите имя контактного лица']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'phone_dir')->textInput(['maxlength' => true, 'placeholder' => 'Введите телефоны']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'email_dir')->textInput(['maxlength' => true, 'placeholder' => 'Введите E-mail']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'post_dir')->textInput(['maxlength' => true, 'placeholder' => 'Введите должность']) ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Перевозчики', ['/ferrymen'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
            <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>

        <?php else: ?>
            <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$urlCheckAtiCode = \yii\helpers\Url::to(['/ferrymen/validate-ati-code']);

$opfhPhys = Opfh::OPFH_ФИЗЛИЦО;
$opfhIp = Opfh::OPFH_ИП;
$opfhOOO = Opfh::OPFH_ООО;

$this->registerJs(<<<JS
$("input").iCheck({
    checkboxClass: "icheckbox_square-green"
});

// Обработчик изменения значения в поле "Наименование".
//
function ferrymenNameOnChange() {
    name = $(this).val();
    if (name != "") {
        if (name.substr(0, 2).toLowerCase() == "ип")
            $("#ferrymen-opfh_id").val($opfhIp).trigger("change");
        else if (name.substr(0, 3).toLowerCase() == "ооо")
            $("#ferrymen-opfh_id").val($opfhOOO).trigger("change");
        else
            $("#ferrymen-opfh_id").val($opfhPhys).trigger("change");
    }
} // ferrymenNameOnChange()

// Обработчик изменения значения в поле "Код АТИ".
//
function atiCodeOnChange() {
    ati_code = $("#ferrymen-ati_code").val();
    if (ati_code != undefined && ati_code != "") {
        \$label = $("#label-ati_code");
        \$label.html("$labelAtiCode &nbsp;<i class=\"fa fa-spinner fa-pulse fa-fw text-primary\"></i>");
        $.get("$urlCheckAtiCode?ati_code=" + ati_code, function(response) {
            label = "$labelAtiCode";
            if (response == true)
                label = "$labelAtiCode &nbsp;<i class=\"fa fa-check-circle-o text-success\"></i>";
            else {
                label = "$labelAtiCode &nbsp;<i class=\"fa fa-times text-danger\"></i>";
                \$label.next().next().text(response.error_description);
            }

            \$label.html(label);
        });        
    }
} // atiCodeOnChange()

$(document).on("change", "#ferrymen-name", ferrymenNameOnChange);
$(document).on("change", "#ferrymen-ati_code", atiCodeOnChange);
JS
, \yii\web\View::POS_READY);
?>
