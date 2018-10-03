<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\ProjectsStates;
use common\models\PostDeliveryKinds;
use common\models\CorrespondencePackagesStates;

/* @var $this yii\web\View */
/* @var $model common\models\CorrespondencePackages */
/* @var $form yii\bootstrap\ActiveForm */

$inputGroupTemplate = "{label}\n<div class=\"input-group\">\n{input}\n<span class=\"input-group-btn\"><button class=\"btn btn-default\" type=\"button\" id=\"btnTrackNumber\"><i class=\"fa fa-search\" aria-hidden=\"true\"></i> Отследить</button></span></div>\n{error}";
$pdDisabled = false;
if (Yii::$app->user->can('sales_department_manager') && $model->cps_id > CorrespondencePackagesStates::STATE_СОГЛАСОВАНИЕ) $pdDisabled = true;
?>

<div class="correspondence-packages-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-5">
            <?php
            $pad = json_decode($model->pad, true);
            if (is_array($pad))
                echo $this->render('_pad', [
                    'model' => $model,
                    'pad' => $pad,
                ]);
            ?>
        </div>
        <div class="col-md-7">
            <?php if (!$model->is_manual): ?>
            <div class="panel panel-success">
                <div class="panel-heading">Проект</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'fo_project_id')->textInput(['disabled' => true]) ?>

                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'customer_name')->textInput(['disabled' => true]) ?>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'stateName')->textInput(['disabled' => true]) ?>

                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'typeName')->textInput(['disabled' => true]) ?>

                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'customer_name')->textInput(['disabled' => true]) ?>

                </div>
                <?php if ($model->cps_id == CorrespondencePackagesStates::STATE_ЧЕРНОВИК || $model->cps_id == CorrespondencePackagesStates::STATE_СОГЛАСОВАНИЕ): ?>
                <div class="col-md-3">
                    <?= $form->field($model, 'manager_id')->widget(Select2::className(), [
                        'data' => \common\models\User::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                    ]) ?>

                </div>
                <?php else: ?>
                <div class="col-md-6">
                    <?= $form->field($model, 'managerProfileName')->textInput(['disabled' => true]) ?>

                </div>
                <?php endif; ?>
            </div>
            <div class="row">
                <?php if (!Yii::$app->user->can('sales_department_manager')): ?>
                <div class="col-md-3">
                    <?= $form->field($model, 'state_id')->widget(Select2::className(), [
                        'data' => ProjectsStates::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                    ]) ?>

                </div>
                <?php endif; ?>
                <div class="col-md-3">
                    <?= $form->field($model, 'pd_id')->widget(Select2::className(), [
                        'data' => PostDeliveryKinds::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'disabled' => $pdDisabled,
                    ]) ?>

                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'track_num', ['template' => $inputGroupTemplate])
                        ->textInput([
                            'maxlength' => true,
                            'placeholder' => 'Введите идентификатор отправления',
                            'title' => 'Введите идентификатор отправления',
                        ]) ?>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'address_id')->widget(Select2::className(), [
                        'data' => $model->arrayMapOfAddressesForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                    ])->label($model->getAttributeLabel('address_id') . ' &nbsp; '.Html::a('<i class="fa fa-plus" aria-hidden="true"></i> добавить', '#', ['id' => 'createNewAddress', 'class' => 'text-success', 'title' => 'Добавить новый почтовый адрес контрагента'])) ?>

                </div>
                <?php if ((Yii::$app->user->can('sales_department_manager') || Yii::$app->user->can('root')) &&
                    ($model->cps_id == CorrespondencePackagesStates::STATE_ЧЕРНОВИК || $model->cps_id == CorrespondencePackagesStates::STATE_СОГЛАСОВАНИЕ)): ?>
                <div class="col-md-6">
                    <?= $form->field($model, 'fo_contact_id')->widget(Select2::className(), [
                        'initValueText' => $model->contact_person != null ? $model->contact_person : '',
                        'data' => $model->fo_id_company != null ? $model->arrayMapOfContactPersonsForSelect2() : [],
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                    ]) ?>

                </div>
                <?php else: ?>
                <div class="col-md-6">
                    <?= $form->field($model, 'contact_person')->textInput(['disabled' => true]) ?>

                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php if (!$model->is_manual): ?>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'pd_id')->widget(Select2::className(), [
                'data' => PostDeliveryKinds::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'track_num', ['template' => $inputGroupTemplate])
                ->textInput([
                    'maxlength' => true,
                    //'disabled' => $model->track_num != null,
                    'placeholder' => 'Введите идентификатор отправления',
                    'title' => 'Введите идентификатор отправления',
                ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'state_id')->widget(Select2::className(), [
                'data' => ProjectsStates::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <div class="col-md-5">
            <?= $form->field($model, 'address_id')->widget(Select2::className(), [
                'data' => $model->arrayMapOfAddressesForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ])->label($model->getAttributeLabel('address_id') . ' &nbsp; '.Html::a('<i class="fa fa-plus" aria-hidden="true"></i> добавить', '#', ['id' => 'createNewAddress', 'class' => 'text-success', 'title' => 'Добавить новый почтовый адрес контрагента'])) ?>

        </div>
    </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'other')->textarea(['rows' => 3, 'placeholder' => 'Введите наименования других документов из этого пакета']) ?>

        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите примечание']) ?>

        </div>
    </div>
    <?php if (
        $model->is_manual &&
        (Yii::$app->user->can('root') || Yii::$app->user->can('sales_department_manager')) &&
        $model->cps_id == \common\models\CorrespondencePackagesStates::STATE_СОГЛАСОВАНИЕ
    ): ?>
    <?= $form->field($model, 'rejectReason')->textarea(['rows' => 3, 'placeholder' => 'Введите причину отказа']) ?>

    <?php endif; ?>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Пакеты корреспонденции', ['/correspondence-packages'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->pd_id == \common\models\PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ && $model->pochta_ru_order_id != null): ?>
        <?= Html::a('<i class="fa fa-barcode"></i> Печать конверта', \backend\controllers\TrackingController::POCHTA_RU_URL_ORDER_PRINT . $model->pochta_ru_order_id, ['title' => 'Печать конверта', 'class' => 'btn btn-lg btn-default', 'target' => '_blank']); ?>

        <?php endif; ?>
        <?php if ($model->is_manual): ?>
        <?= Html::a('<i class="fa fa-history"></i> История', ['#block-history'], ['class' => 'btn btn-default btn-lg', 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'block-history']) ?>

        <?php endif; ?>
        <?= $model->renderSubmitButtons() ?>

    </div>
    <?= $form->field($model, 'fo_id_company', ['template' => '{input}'])->hiddenInput()->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
<div id="mw_summary" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header"><h4 id="modal_title" class="modal-title">Modal title</h4></div>
            <div id="modal_body" class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<?php
$url = Url::to(['/tracking/track-by-number']);
$url_create_address = Url::to(['/correspondence-packages/create-address-form']);
$urlNormalizeAddress = Url::to(['/tracking/normalize-address']);

$formName = $model->formName();
$formNameId = strtolower($model->formName());
$name = $model->formName() . '[tpPad]';
$stateSent = ProjectsStates::STATE_ОТПРАВЛЕНО;

$this->registerJs(<<<JS
var checked = false;
$("input").iCheck({
    checkboxClass: 'icheckbox_square-green',
});

// Обработчик щелчка по ссылке "Отметить все документы".
//
function checkAllDocumentsOnClick() {
    if (checked) {
        operation = "uncheck";
        checked = false;
    }
    else {
        operation = "check";
        checked = true;
    }

    $("input[name ^= '$name']").iCheck(operation);

    return false;
} // checkAllDocumentsOnClick()

// Обработчик щелчка по ссылке "Отметить наиболее распространенные документы".
//
function checkRegularDocumentsOnClick() {
    var values = ["1", "2", "3" , "4"];
    $("input[name ^= '$name']").iCheck("uncheck");
    $.each(values, function(index, value) {
        $("input[data-id = '" + value + "'").iCheck("check");
    });

    return false;
} // checkRegularDocumentsOnClick()

// Обработчик щелчка по кнопке "Отследить".
//
function btnTrackNumberOnClick() {
    pd = $("#$formNameId-pd_id").val();
    tracknum = $("#$formNameId-track_num").val();
    if (tracknum != "" && tracknum != undefined && pd != "" && pd != undefined) {
        $("#modal_title").text("Трекинг");
        $("#modal_body").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#mw_summary").modal();
        $("#modal_body").load("$url?pd_id=" + pd + "&track_num=" + tracknum);
    }

    return false;
} // btnTrackNumberOnClick()

// Обработчик изменения значения в поле "Трек-номер".
//
function trackNumberOnChange() {
    $("#$formNameId-state_id").val("$stateSent").trigger("change");
} // trackNumberOnChange()

// Обработчик изменения значения в поле "Оригинальный адрес" в модальном окне.
//
function srcAddressOnChange() {
    address = $("#counteragentspostaddresses-src_address").val();
    $.get("$urlNormalizeAddress", {address: address}, function(data) {
        if (data != false) {
            $("#counteragentspostaddresses-zip_code").val(data.index);
            $("#counteragentspostaddresses-address_m").val(data.address);
        }
    });
} // srcAddressOnChange()

// Обработчик щелчка по ссылке "Добавить новый почтовый адрес контрагента".
//
function createNewAddressOnClick() {
    ca_id = $("#$formNameId-fo_id_company").val();
    if (ca_id != "" && ca_id != undefined) {
        $("#modal_title").text("Новый почтовый адрес");
        $("#modal_body").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-success"></i><span class="sr-only">Подождите...</span></p>');
        $("#mw_summary").modal();
        $("#modal_body").load("$url_create_address?id=$model->id&ca_id=" + ca_id);
    }

    return false;
} // createNewAddressOnClick()

// Обработчик щелчка по кнопке "Создать" в форме добавления нового адреса.
// Форма отправляется на сервер, если создание выполнено успешно, то модальное окно закроется и созданный элемент
// будет добавлен и выбран в поле "Адрес" формы пакета корреспонденции.
//
function btnSubmitOnClick() {
    $.ajax({
        type: "POST",
        url: "$url_create_address",
        data: $("#frmNewPostAddress").serialize(),
        success: function(response) {
            if (response != false) {
                $("#mw_summary").modal("toggle");
                var newOption = new Option(response.address, response.id, true, true);
                $("#correspondencepackages-address_id").append(newOption).trigger("change");
            }
        }
    });
} // btnSubmitOnClick

$("#pjax-form").on("pjax:end", function() {
    $.pjax.reload({container:"#pjax-address"});
});

$(document).on("click", "#checkAllDocuments", checkAllDocumentsOnClick);
$(document).on("click", "#checkRegularDocuments", checkRegularDocumentsOnClick);
$(document).on("click", "#btnTrackNumber", btnTrackNumberOnClick);
$(document).on("click", "#createNewAddress", createNewAddressOnClick);
$(document).on("click", "#btnSubmit", btnSubmitOnClick);
$(document).on("change", "#$formNameId-track_num", trackNumberOnChange);
$(document).on("change", "#counteragentspostaddresses-src_address", srcAddressOnChange);
JS
, \yii\web\View::POS_READY);
?>
