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

    <div class="row">
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'inn')->widget(MaskedInput::class, [
                        'mask' => '999999999999',
                        'clientOptions' => ['placeholder' => ''],
                    ])->textInput(['maxlength' => true, 'placeholder' => 'Введите ИНН'])
                        ->label($label_inn, ['id' => 'label-inn']) ?>

                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'kpp')->widget(MaskedInput::class, [
                        'mask' => '999999999',
                        'clientOptions' => ['placeholder' => ''],
                    ])->textInput(['maxlength' => true, 'placeholder' => 'Введите КПП']) ?>

                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'ogrn')->widget(MaskedInput::class, [
                        'mask' => '999999999999999',
                        'clientOptions' => ['placeholder' => ''],
                    ])->textInput(['maxlength' => true, 'placeholder' => 'Введите ОГРН или ОГРНИП', 'title' => 'Введите ОГРН или ОГРНИП'])
                        ->label($label_ogrn, ['id' => 'label-ogrn']) ?>

                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'doc_num_tmpl')->textInput(['placeholder' => 'Шаблон номера договоров'])->label('Шабл. № договора') ?>

                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'im_num_tmpl')->textInput(['placeholder' => 'Введите шаблон', 'title' => 'Шаблон номера для входящей корреспонденции'])->label('Шабл. № вх. корр.') ?>

                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'om_num_tmpl')->textInput(['placeholder' => 'Введите шаблон', 'title' => 'Шаблон номера для исходящей корреспонденции'])->label('Шабл. № исх. корр.') ?>

                </div>
            </div>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'license_req')->textInput(['placeholder' => '№ 050 079 от «30» августа 2017 г.']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'fo_dt_id')->widget(MaskedInput::class, [
                'mask' => '99',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите число', 'title' => 'Введите тип документа, по которому будет идентифицирована эта организация при импорте счетов из Fresh Office']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Введите наименование']) ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'name_short')->textInput(['maxlength' => true, 'placeholder' => 'Например, ООО "Ромашка"']) ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'name_full')->textInput(['maxlength' => true, 'placeholder' => 'Например, Общество с ограниченной ответственностью "Ромашка"']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'address_j')->textInput(['placeholder' => 'Введите юридический адрес']) ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'address_f')->textInput(['placeholder' => 'Введите фактический адрес']) ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'address_ttn')->textInput(['placeholder' => 'Введите адрес для ТТН']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'phones')->textInput(['placeholder' => 'Введите телефоны']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'email')->textInput(['placeholder' => 'Введите E-mail']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'dir_post')->textInput(['maxlength' => true, 'placeholder' => 'Введите должность директора']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'dir_name')->textInput(['maxlength' => true, 'placeholder' => 'Введите ФИО директора полностью']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'dir_name_short')->textInput(['maxlength' => true, 'placeholder' => 'Введите сокрашенные ФИО директора']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'dir_name_of')->textInput(['maxlength' => true, 'placeholder' => 'Директор в родительном падеже', 'title' => 'Введите полные ФИО директора в родительном падеже']) ?>

        </div>
    </div>
    <div class="form-group">
        <p class="text-muted">Примечание. Описание переменных для шаблонов номеров документов и входящей корреспонденции.</p>
        <p class="text-muted"><strong>[D]</strong> - день, <strong>[M]</strong> - месяц, <strong>[Y]</strong> - год, <strong>[C<em>n</em>]</strong> - счетчик, где <strong><em>n</em></strong> - общее количество символов в цифре с ведущими нулями.</p>
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

    \$field = $("#$formName-dir_name");
    \$field.val("");
    if (caInfo.dir_name) \$field.val(caInfo.dir_name);

    \$field = $("#$formName-dir_name_of");
    \$field.val("");
    if (caInfo.dir_name_of) \$field.val(caInfo.dir_name_of);

    \$field = $("#$formName-dir_name_short");
    \$field.val("");
    if (caInfo.dir_name_short) \$field.val(caInfo.dir_name_short);

    \$field = $("#$formName-dir_post");
    \$field.val("");
    if (caInfo.dir_post) \$field.val(caInfo.dir_post);
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
            $.get("$url_inn_ogrn?query=" + inn + "&specifyingValue=&cleanDir=1", function(response) {
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
            $.get("$url_inn_ogrn?query=" + ogrn + "&cleanDir=1", function(response) {
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
