<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model common\models\Organizations */
/* @var $form yii\bootstrap\ActiveForm */

$label_inn = $model->attributeLabels()['inn'];
$label_ogrn = $model->attributeLabels()['ogrn'];
?>

<div class="organizations-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Введите наименование']) ?>

    <?= $form->field($model, 'name_short')->textInput(['maxlength' => true, 'placeholder' => 'Например, ООО "Ромашка"']) ?>

    <?= $form->field($model, 'name_full')->textInput(['maxlength' => true, 'placeholder' => 'Например, Общество с ограниченной ответственностью "Ромашка"']) ?>

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
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Организации', ['/organizations'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

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

$(document).on("change", "#$formName-inn", innOnChange);
$(document).on("change", "#$formName-ogrn", ogrnOnChange);
JS
, yii\web\View::POS_READY);
?>
