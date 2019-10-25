<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\OrganizationsBas */
/* @var $form yii\bootstrap\ActiveForm */

$formName = strtolower($model->formName());
$label_bank_bik = $model->attributeLabels()['bank_bik'];
$url_bank_bik = Url::to(['/services/fetch-bank-by-bik']);
?>

<div class="bank-account-form">
    <div class="panel panel-success">
        <div class="panel-heading">Добавление нового банковского счета</div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin([
                'id' => 'frmNewBankAccount',
                'action' => \backend\controllers\OrganizationsController::URL_ADD_BANK_ACCOUNT_AS_ARRAY,
                'options' => ['data-pjax' => true],
            ]); ?>

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
            <?= $form->field($model, 'org_id')->hiddenInput()->label(false) ?>

            <div class="form-group">
                <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Добавить', ['class' => 'btn btn-success']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
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

$(document).on("change", "#$formName-bank_bik", bankBikOnChange);
JS
, \yii\web\View::POS_READY);
?>
