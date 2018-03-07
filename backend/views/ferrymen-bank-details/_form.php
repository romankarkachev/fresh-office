<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model common\models\FerrymenBankDetails */
/* @var $form yii\bootstrap\ActiveForm */

$label_inn = $model->attributeLabels()['inn'];
$label_ogrn = $model->attributeLabels()['ogrn'];
$label_bank_bik = $model->attributeLabels()['bank_bik'];
?>

<div class="ferrymen-bank-details-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ferryman_id')->hiddenInput()->label(false) ?>

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
        <div class="col-md-3">
            <?= $form->field($model, 'ogrn')->widget(MaskedInput::className(), [
                'mask' => '999999999999999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите ОГРН или ОГРНИП', 'title' => 'Введите ОГРН или ОГРНИП'])
                ->label($label_ogrn, ['id' => 'label-ogrn']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'contract_num')->textInput(['maxlength' => true, 'placeholder' => 'Введите № договора']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'contract_date')->widget(DateControl::className(), [
                'value' => $model->contract_date,
                'type' => DateControl::FORMAT_DATE,
                'language' => 'ru',
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'options' => ['placeholder' => 'выберите дату'],
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
        <div class="col-md-4">
            <?= $form->field($model, 'name_full')->textInput(['maxlength' => true, 'placeholder' => 'Введите наименование организации']) ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'address_j')->textInput(['placeholder' => 'Введите юридический адрес']) ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'address_f')->textInput(['placeholder' => 'Введите фактический адрес']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'bank_bik')->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '999999999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput([
                'placeholder' => 'Введите БИК банка',
                'title' => 'Вставьте БИК банка и другие реквизиты могут быть подставлены автоматически'
            ])->label($label_bank_bik, ['id' => 'label-bank_bik']) ?>

        </div>
        <div class="col-md-3 col-lg-2">
            <?= $form->field($model, 'bank_an')->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '99999999999999999999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['placeholder' => 'Введите номер расчетного счета']) ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'bank_name')->textInput(['maxlength' => true, 'placeholder' => 'Введите наименование банка']) ?>

        </div>
        <div class="col-md-3 col-lg-2">
            <?= $form->field($model, 'bank_ca')->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '99999999999999999999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['placeholder' => 'Введите номер корр. счета']) ?>

        </div>
    </div>
    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите комментарии']) ?>

    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . $model->ferryman->name, ['/ferrymen/update', 'id' => $model->ferryman->id], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в карточку перевозчика. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
            <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>

        <?php else: ?>
            <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$url_inn_ogrn = Url::to(['/services/fetch-counteragents-info-dadata']);
$url_bank_bik = Url::to(['/services/fetch-bank-by-bik']);

$formName = strtolower($model->formName());
$this->registerJs(<<<JS
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

    \$field = $("#$formName-name_full");
    \$field.val("");
    if (caInfo.name_short) \$field.val(caInfo.name_short);

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
            $.get("$url_inn_ogrn?query=" + inn, function(response) {
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
            $.get("$url_inn_ogrn?query=" + ogrn, function(response) {
                if (response != false) {
                    fillFields(response);
                }
    
            }).always(function() {
                \$label.html("$label_ogrn");
            });
        }
    }
} // ogrnOnChange()

// Обработчик изменения значения в поле "БИК банка".
//
function bankBikOnChange() {
    bik = $(this).val();
    if (bik.length == 9) {
        $("#$formName-bank_ca").val("");
        $("#$formName-bank_name").val("");
        $("#label-bank_bik").html("$label_bank_bik &nbsp;<i class=\"fa fa-spinner fa-pulse fa-fw text-primary\"></i>");
        $.get("$url_bank_bik?bik=" + bik, function(response) {
            if (response != false) {
                $("#$formName-bank_ca").val(response.bank_ca);
                $("#$formName-bank_name").val(response.bank_name);
            };

        }).always(function() {
            $("#label-bank_bik").html("$label_bank_bik");
        });
    }
} // bankBikOnChange()

$(document).on("change", "#$formName-inn", innOnChange);
$(document).on("change", "#$formName-ogrn", ogrnOnChange);
$(document).on("change", "#$formName-bank_bik", bankBikOnChange);
JS
, yii\web\View::POS_READY);
?>
