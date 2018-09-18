<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use yii\widgets\MaskedInput;
use common\models\Ferrymen;
use common\models\FerrymenTypes;
use common\models\PaymentConditions;
use common\models\Opfh;

/* @var $this yii\web\View */
/* @var $model common\models\Ferrymen */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dpDrivers common\models\Drivers[] */
/* @var $dpTransport common\models\Transport[] */

$label_inn = $model->attributeLabels()['inn'];
$label_ogrn = $model->attributeLabels()['ogrn'];
$labelAtiCode = $model->attributeLabels()['ati_code'];
?>

<div class="ferrymen-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Введите наименование', 'readonly' => true]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'name_crm')->textInput(['maxlength' => true, 'placeholder' => 'Введите наименование из CRM']) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'name_full')->textInput(['placeholder' => 'Введите полное наименование', 'readonly' => true]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'name_short')->textInput(['placeholder' => 'Введите сокращенное наименование организации', 'readonly' => true]) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'inn')->widget(MaskedInput::className(), [
                'mask' => '999999999999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите ИНН'])
                ->label($label_inn, ['id' => 'label-inn']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'kpp')->widget(MaskedInput::className(), [
                'mask' => '999999999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите КПП']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'ogrn')->widget(MaskedInput::className(), [
                'mask' => '999999999999999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите ОГРН или ОГРНИП', 'title' => 'Введите ОГРН или ОГРНИП'])
                ->label($label_ogrn, ['id' => 'label-ogrn']) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'address_j')->textInput(['placeholder' => 'Введите юридический адрес', 'readonly' => true]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'address_f')->textInput(['placeholder' => 'Введите фактический адрес']) ?>

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
        <div class="col-md-2">
            <?= $form->field($model, 'contract_expires_at')->widget(DateControl::className(), [
                'value' => $model->contract_expires_at,
                'type' => DateControl::FORMAT_DATE,
                'language' => 'ru',
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'options' => ['placeholder' => '- выберите -'],
                    'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                    'layout' => '<div class="input-group">{input}{picker}</div>',
                    'pluginOptions' => [
                        'todayHighlight' => true,
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ]) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'ppdq')->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '999999999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['placeholder' => 'Введите число']) ?>

        </div>
    </div>
    <?= $form->field($model, 'notify_when_payment_orders_created')->checkbox()->label('Отправлять уведомление при импорте ордеров', [
        'title' => 'Необходимость отправлять уведомление перевозчику при импорте платежного ордера на него',
        'style' => 'padding-left: 0px;'
    ]) ?>

    <div class="row">
        <div class="col-md-12"><p class="lead">Диспетчер</p></div>
        <div class="col-md-2">
            <?= $form->field($model, 'contact_person')->textInput(['maxlength' => true, 'placeholder' => 'Введите имя', 'title' => 'Введите имя контактного лица']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'phone', ['template' => '{label}<div class="input-group"><span class="input-group-addon">+7</span>{input}</div>{error}'])->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '(999) 999-99-99',
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите номер телефона']) ?>

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
            <?= $form->field($model, 'phone_dir', ['template' => '{label}<div class="input-group"><span class="input-group-addon">+7</span>{input}</div>{error}'])->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '(999) 999-99-99',
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите номер телефона']) ?>

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
        <?php if ($model->user_id == null): ?>
        <?= Html::a('<i class="fa fa-paper-plane"></i> Пригласить перевозчика', '#', [
            'class' => 'btn btn-default btn-lg',
            'id' => 'btnInviteFerryman',
            'title' => 'Открыть форму отправки приглашения создать аккаунт в личном кабинете',
        ]) ?>

        <?php endif; ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<div id="modalWindow" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="modal_title" class="modal-title">Modal title</h4>
            </div>
            <div id="modal_body" class="modal-body">
                <p>One fine body…</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<?php
$formName = strtolower($model->formName());
$urlFetchCounteragentsInfo = Url::to(['/services/fetch-counteragents-info-dadata']);
$urlCheckAtiCode = Url::to(['/ferrymen/validate-ati-code']);
$urlFerrymanInvitationForm = Url::to(['/ferrymen/invite-ferryman-form']);
$urlSendInvitation = Url::to(['/ferrymen/send-invitation']);

$opfhPhys = Opfh::OPFH_ФИЗЛИЦО;
$opfhIp = Opfh::OPFH_ИП;
$opfhOOO = Opfh::OPFH_ООО;

$this->registerJs(<<<JS
$("input").iCheck({checkboxClass: "icheckbox_square-green"});

// Заполняет реквизиты данными, полученными через механизм API.
//
function fillFields(caInfo) {
    \$field = $("#$formName-inn");
    \$field.val("");
    if (caInfo.inn) \$field.val(caInfo.inn);

    \$field = $("#$formName-kpp");
    \$field.val("");
    if (caInfo.kpp) \$field.val(caInfo.kpp);

    \$field = $("#$formName-ogrn");
    \$field.val("");
    if (caInfo.ogrn) \$field.val(caInfo.ogrn);

    \$field = $("#$formName-name");
    \$field.val("");
    if (caInfo.name) \$field.val(caInfo.name);

    \$field = $("#$formName-name_short");
    \$field.val("");
    if (caInfo.name_short) \$field.val(caInfo.name_short);

    \$field = $("#$formName-name_full");
    \$field.val("");
    if (caInfo.name_full) \$field.val(caInfo.name_full);

    \$field = $("#$formName-address_j");
    \$field.val("");
    if (caInfo.address) \$field.val(caInfo.address);
} // fillFields()

// Обработчик изменения значения в поле "ИНН".
//
function innOnChange() {
    ogrn = $("#$formName-ogrn").val();
    kpp = $("#$formName-kpp").val();
    if (ogrn == "" || kpp == "") {
        inn = $("#$formName-inn").val();
        if (inn != "") {
            \$label = $("#label-inn");
            \$label.html("$label_inn &nbsp;<i class=\"fa fa-spinner fa-pulse fa-fw text-primary\"></i>");
            $.get("$urlFetchCounteragentsInfo?query=" + inn, function(response) {
                if (response != false) {
                    fillFields(response);
                }
    
            }).always(function() {
                \$label.html("$label_inn");
            });
        }
    }
} // innOnChange()

// Обработчик изменения значения в поле "ОГРН".
//
function ogrnOnChange() {
    inn = $("#$formName-inn").val();
    kpp = $("#$formName-kpp").val();
    if (inn == "" || kpp == "") {
        ogrn = $("#$formName-ogrn").val();
        if (ogrn != "") {
            \$label = $("#label-ogrn");
            \$label.html("$label_ogrn &nbsp;<i class=\"fa fa-spinner fa-pulse fa-fw text-primary\"></i>");
            $.get("$urlFetchCounteragentsInfo?query=" + ogrn, function(response) {
                if (response != false) {
                    fillFields(response);
                }
    
            }).always(function() {
                \$label.html("$label_ogrn");
            });
        }
    }
} // ogrnOnChange()

// Обработчик изменения значения в поле "Наименование".
//
function ferrymenNameOnChange() {
    name = $(this).val();
    if (name != "") {
        if (name.substr(0, 2).toLowerCase() == "ип")
            $("#$formName-opfh_id").val($opfhIp).trigger("change");
        else if (name.substr(0, 3).toLowerCase() == "ооо")
            $("#$formName-opfh_id").val($opfhOOO).trigger("change");
        else
            $("#$formName-opfh_id").val($opfhPhys).trigger("change");
    }
} // ferrymenNameOnChange()

// Обработчик изменения значения в поле "Код АТИ".
//
function atiCodeOnChange() {
    ati_code = $("#$formName-ati_code").val();
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

// Обработчик щелчка по кнопке "Пригласить перевозчика".
// Отображает форму отправки приглашения.
//
function btnInviteFerrymanOnClick() {
    $("#modal_title").text("Пригласить перевозчика создать аккаунт");
    $("#modal_body").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
    $("#modalWindow").modal();
    $("#modal_body").load("$urlFerrymanInvitationForm?id=$model->id");

    return false;
} // btnInviteFerrymanOnClick()

// Обработчик щелчка по кнопке "Отправить приглашение".
//
function btnSendInvitationOnClick() {
    \$block = $("#spanButtonSubmit");
    \$block.replaceWith('<span id="spanButtonSubmit" class="input-group-addon"><i class="fa fa-spinner fa-pulse fa-fw text-primary"></i><span class="sr-only">Подождите...</span></span>');
    $.post("$urlSendInvitation", $("#frmInviteFerryman").serialize(), function(result) {
        if (result == true) $("#spanButtonSubmit").replaceWith('<span id="spanButtonSubmit" class="input-group-addon"><i class="fa fa-check-circle text-success" aria-hidden="true"></i> Приглашение отправлено.</span>');
    });
} // btnSendInvitationOnClick()

$(document).on("change", "#$formName-inn", innOnChange);
$(document).on("change", "#$formName-ogrn", ogrnOnChange);
$(document).on("change", "#$formName-name", ferrymenNameOnChange);
$(document).on("change", "#$formName-ati_code", atiCodeOnChange);
$(document).on("click", "#btnInviteFerryman", btnInviteFerrymanOnClick);
$(document).on("click", "#btnSendInvitation", btnSendInvitationOnClick);
JS
, \yii\web\View::POS_READY);
?>
