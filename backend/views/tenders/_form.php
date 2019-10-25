<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use yii\web\JsExpression;
use backend\controllers\TendersController;
use common\models\TendersPlatforms;
use common\models\TendersApplications;
use common\models\TendersKinds;
use common\models\WasteEquipment;
use common\models\Organizations;
use common\models\User;
use common\models\Tenders;
use common\models\TendersTp;

/* @var $this yii\web\View */
/* @var $model common\models\Tenders */
/* @var $waste array|\yii\data\ActiveDataProvider of common\models\TendersTp */
/* @var $form yii\bootstrap\ActiveForm */

$emptyWasteBlockPrompt = '<p class="card-text text-muted">Табличная часть отходов пуста.</p>';

if ($model->crudeWaste instanceof \yii\data\ActiveDataProvider) {
    $wasteCount = $model->crudeWaste->getTotalCount();
}
elseif (is_array($model->crudeWaste))
    $wasteCount = count($model->crudeWaste);
else
    $wasteCount = 0;

$formName = $model->formName();
$formNameId = strtolower($formName);

$urlFindTenderByNumber = Url::to(TendersController::FIND_TENDER_BY_NUMBER_AS_ARRAY);
$urlAddWasteRow = Url::to(TendersController::URL_RENDER_WASTE_AS_ARRAY);
$urlFetchLicenseRequests = Url::to(TendersController::URL_LR_CASTING_AS_ARRAY);
$urlFillFkko = Url::to(TendersController::URL_FILL_FKKO_AS_ARRAY);

$blockWasteId = TendersTp::DOM_IDS['BLOCK_ID'];
$wasteRowId = TendersTp::DOM_IDS['ROW_ID'];
$wastePreloaderId = TendersTp::DOM_IDS['PRELOADER'];
$btnAddWasteId = TendersTp::DOM_IDS['ADD_BUTTON'];
$btnDeleteWasteId = TendersTp::DOM_IDS['DELETE_BUTTON'];

// наименование контрагента для вывода в форме
// если оно явно указано в соответствующем поле, то выводим его
// если не указано - вычисляем дополнительным запросом к другому источнику данных (MS SQL)
$initValueText = '';
if (empty($model->fo_ca_name)) {
    if (!empty($model->fo_ca_id)) {
        $company = \common\models\foCompany::findOne($model->fo_ca_id);
        if ($company) {
            $initValueText = trim($company->COMPANY_NAME);
            unset($company);
        }
    }
}
else {
    $initValueText = $model->fo_ca_name;
}
?>

<div class="tenders-form">
    <?php $form = ActiveForm::begin(['id' => 'frmTender']); ?>

    <?php if ($model->isNewRecord): ?>
    <div class="panel panel-success">
        <div class="panel-heading"></div>
        <div class="panel-body">
            <ul class="nav nav-pills">
                <li class="active"><a data-toggle="pill" href="#find_number" data-value="0">По номеру ООС</a></li>
                <li><a data-toggle="pill" href="#find_manual" data-value="1">Вручную</a></li>
            </ul>
            <div class="tab-content">
                <div id="find_number" class="tab-pane fade in active" style="padding-top: 10px;">
                    <div class="row">
                        <div class="col-md-3">
                            <?= $form->field($model, 'urlSource')->textInput(['title' => 'Вставьте ссылку, например, http://zakupki.gov.ru/epz/order/notice/ea44/view/common-info.html?regNumber=0367100005219000094', 'placeholder' => 'Вставьте ссылку']) ?>

                        </div>
                    </div>
                </div>
                <div id="find_manual" class="tab-pane fade" style="padding-top: 10px;">
                    <div class="row">
                        <div class="col-md-2">
                            <?= $form->field($model, 'ftInn')->widget(MaskedInput::className(), [
                                'mask' => '999999999999',
                                'clientOptions' => ['placeholder' => ''],
                            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите ИНН']) ?>

                        </div>
                        <div class="col-md-2">
                            <?= $form->field($model, 'ftKpp')->widget(MaskedInput::className(), [
                                'mask' => '999999999',
                                'clientOptions' => ['placeholder' => ''],
                            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите КПП']) ?>

                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'ftTitle')->textInput(['placeholder' => 'Введите часть наименования закупки']) ?>

                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0px;">
                <?= Html::button('<i class="fa fa-search" aria-hidden="true"></i> Найти', ['id' => 'findTender', 'class' => 'btn btn-default', 'title' => 'Найти закупку по введенным параметрам', 'data-loading-text' => '<i class="fa fa-cog fa-spin fa-lg text-info"></i> Операция выполняется...']) ?>

            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'org_id')->widget(Select2::className(), [
                'data' => Organizations::arrayMapForSelect2(2),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ])->label('Участвуем от') ?>

        </div>
        <div class="col-md-2">
            <?php if (!Yii::$app->user->can('sales_department_manager')): ?>
            <?= $form->field($model, 'tp_id')->widget(Select2::class, [
                'data' => TendersPlatforms::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

            <?php else: ?>
            <?= $form->field($model, 'tp_id', ['template' => '{label}{input}<span id="tp_id" class="form-control text-muted" readonly="readonly">- не определено -</span>{error}'])->hiddenInput() ?>

            <?php endif; ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'ta_id')->widget(Select2::className(), [
                'data' => TendersApplications::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'tk_id')->widget(Select2::class, [
                'data' => TendersKinds::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-4">
            <?php if (!Yii::$app->user->can('tenders_manager')): ?>
            <?= $form->field($model, 'fo_ca_id')->widget(Select2::className(), [
                'initValueText' => $initValueText,
                'theme' => Select2::THEME_BOOTSTRAP,
                'language' => 'ru',
                'options' => ['placeholder' => 'Введите наименование'],
                'pluginOptions' => [
                    'minimumInputLength' => 1,
                    'language' => 'ru',
                    'ajax' => [
                        'url' => Url::to(['projects/direct-sql-counteragents-list']),
                        'delay' => 500,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(result) { return result.text; }'),
                    'templateSelection' => new JsExpression('function (result) { return result.text; }'),
                ],
                'pluginEvents' => [
                    'change' => new JsExpression('function() {fetchLicenseRequests();}'),
                ],
            ]) ?>
            <?php else: ?>
            <?= $form->field($model, 'fo_ca_name')->textInput(['placeholder' => 'Введите наименование заказчика', 'title' => 'Наименование определяется автоматически из закупки, но также возможно ввести его вручную'])->label('Наименование заказчика') ?>
            <?php endif; ?>

        </div>
    </div>
    <div class="row">
        <?php if (!Yii::$app->user->can('sales_department_manager')): ?>
        <div class="col-md-2">
            <?= $form->field($model, 'manager_id')->widget(Select2::className(), [
                'data' => User::arrayMapForSelect2(User::ARRAY_MAP_OF_USERS_BY_ALL_ROLES),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <?php else: ?>
        <?= $form->field($model, 'manager_id')->hiddenInput()->label(false) ?>

        <?php endif; ?>
        <?php if (Yii::$app->user->can('root')): ?>
        <div class="col-md-2">
            <?= $form->field($model, 'state_id')->widget(Select2::className(), [
                'data' => \common\models\TendersStates::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ])->label('Статус') ?>

        </div>
        <?php endif; ?>
        <div class="col-md-2">
            <?= $form->field($model, 'placed_at')->widget(DateControl::class, [
                'value' => $model->placed_at,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:U',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => ['placeholder' => '- выберите дату -', 'title' => $model->getAttributeLabel('placed_at'), 'autocomplete' => 'off'],
                    'pluginOptions' => ['weekStart' => 1, 'autoclose' => true],
                ],
                'disabled' => true,
            ])->label('Размещено') ?>

        </div>
        <!--
        <div class="col-md-2">
            <?= $form->field($model, 'date_complete')->widget(DateControl::className(), [
                'value' => $model->date_complete,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => ['placeholder' => '- выберите дату -', 'title' => $model->getAttributeLabel('date_complete'), 'autocomplete' => 'off'],
                    'pluginOptions' => ['weekStart' => 1, 'autoclose' => true],
                ],
            ])->label('Срок выполнения') ?>

        </div>
        -->
        <div class="col-md-2">
            <?= $form->field($model, 'date_stop')->widget(DateControl::className(), [
                'value' => $model->date_stop,
                'type' => DateControl::FORMAT_DATETIME,
                'displayFormat' => 'php:d.m.Y H:i',
                'saveFormat' => 'php:U',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => ['placeholder' => '- выберите дату -', 'title' => $model->getAttributeLabel('date_stop'), 'autocomplete' => 'off'],
                    'pluginOptions' => ['weekStart' => 1, 'autoclose' => true],
                ],
                'disabled' => Yii::$app->user->can('sales_department_manager') || Yii::$app->user->can('sales_department_head'),
            ])->label('Окончание приема') ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'date_sumup')->widget(DateControl::className(), [
                'value' => $model->date_sumup,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:U',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => ['placeholder' => '- выберите дату -', 'title' => $model->getAttributeLabel('date_sumup'), 'autocomplete' => 'off'],
                    'pluginOptions' => ['weekStart' => 1, 'autoclose' => true],
                ],
                'disabled' => Yii::$app->user->can('sales_department_manager') || Yii::$app->user->can('sales_department_head'),
            ])->label('Подведение итогов') ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'date_auction')->widget(DateControl::className(), [
                'value' => $model->date_auction,
                'type' => DateControl::FORMAT_DATETIME,
                'displayFormat' => 'php:d.m.Y H:i',
                'saveFormat' => 'php:U',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => ['placeholder' => '- выберите дату -', 'title' => $model->getAttributeLabel('date_auction'), 'autocomplete' => 'off'],
                    'pluginOptions' => ['weekStart' => 1, 'autoclose' => true],
                ],
                'disabled' => Yii::$app->user->can('sales_department_manager') || Yii::$app->user->can('sales_department_head'),  // менеджеру это поле недоступно
            ])->label('Аукцион') ?>

        </div>
    </div>
    <?= $form->field($model, 'title')->textarea(['rows' => 3, 'placeholder' => 'Введите наименование закупки']) ?>

    <?= $form->field($model, 'conditions')->textarea(['rows' => 3, 'placeholder' => 'Введите особые требования (например: только размещение, самопривоз, применение конкретной технологии и т.д.)']) ?>

    <div class="row">
        <!--
        <div class="col-md-2">
            <?= $form->field($model, 'responsible_id')->widget(Select2::className(), [
                'data' => User::arrayMapForSelect2(User::ARRAY_MAP_OF_USERS_BY_MANAGER_AND_ECOLOGIST_ROLE),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        -->
        <div class="col-md-3">
            <?= $form->field($model, 'we')->widget(Select2::className(), [
                'data' => WasteEquipment::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -', 'multiple' => true],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-1" title="<?= $model->getAttributeLabel('is_notary_required') ?>">
            <label for="<?= $formName ?>-is_notary_required" class="control-label">Нотариус</label>
            <?= $form->field($model, 'is_notary_required')->checkbox()->label(false) ?>

        </div>
        <div class="col-md-1" title="<?= $model->getAttributeLabel('is_contract_edit') ?>">
            <label for="<?= $formName ?>-is_contract_edit" class="control-label">Изменения</label>
            <?= $form->field($model, 'is_contract_edit')->checkbox()->label(false) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'amount_start', [
                'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
            ])->widget(MaskedInput::className(), [
                'clientOptions' => [
                    'alias' =>  'numeric',
                    'groupSeparator' => ' ',
                    'autoUnmask' => true,
                    'autoGroup' => true,
                    'removeMaskOnSubmit' => true,
                ],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => '0',
                'title' => $model->getAttributeLabel('amount_start'),
            ])->label('НМЦ') ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'amount_offer', [
                'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
            ])->widget(MaskedInput::className(), [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'digits' => 2,
                    'groupSeparator' => ' ',
                    'autoUnmask' => true,
                    'autoGroup' => true,
                    'removeMaskOnSubmit' => true,
                ],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => '0',
                'title' => $model->getAttributeLabel('amount_offer'),
            ])->label('Наша цена') ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'amount_fo', [
                'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
            ])->widget(MaskedInput::class, [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'digits' => 2,
                    'groupSeparator' => ' ',
                    'autoUnmask' => true,
                    'autoGroup' => true,
                    'removeMaskOnSubmit' => true,
                ],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => '0',
                'title' => $model->getAttributeLabel('amount_fo'),
                'readonly' => Yii::$app->user->can('sales_department_manager') || Yii::$app->user->can('sales_department_head'),
            ])->label('Обесп. заявки') ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'amount_fc', [
                'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
            ])->widget(MaskedInput::class, [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'digits' => 2,
                    'groupSeparator' => ' ',
                    'autoUnmask' => true,
                    'autoGroup' => true,
                    'removeMaskOnSubmit' => true,
                ],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => '0',
                'title' => $model->getAttributeLabel('amount_fc'),
                'readonly' => Yii::$app->user->can('sales_department_manager') || Yii::$app->user->can('sales_department_head'),
            ])->label('Обесп. контракта') ?>

        </div>
        <!--
        <div class="col-md-2">
            <?= $form->field($model, 'deferral')->widget(MaskedInput::className(), [
                'mask' => '9999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите число', 'title' => $model->getAttributeLabel('deferral')])->label('Отсрочка') ?>

        </div>
        -->
        <div class="col-md-2">
            <?= $form->field($model, 'is_contract_approved')->widget(Select2::className(), [
                'data' => Tenders::arrayMapOfIsContractApprovedForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <?php if ($model->isNewRecord): ?><div class="panel panel-success">
        <div class="panel-heading">
            <small>Отходы</small>
            <span id="<?= $wastePreloaderId ?>" class="collapse"><i class="fa fa-cog fa-spin text-muted"></i></span>
                <?= Html::button('<i class="fa fa-plus"></i> Добавить', [
                    'id' => TendersTp::DOM_IDS['ADD_BUTTON'],
                    'class' => 'btn btn-default btn-xs pull-right',
                    'data-count' => $wasteCount,
                ]) ?>

        </div>
        <div class="panel-body">
            <div id="block-lr"></div>
            <div id="<?= $blockWasteId ?>">
                <?php
                if ($wasteCount == 0) {
                    echo $emptyWasteBlockPrompt;
                }
                else {
                    foreach ($model->crudeWaste as $index => $row)
                        echo $this->render('_row_waste_fields', [
                            'model' => $row,
                            'parentModel' => $model,
                            'form' => $form,
                            'counter' => $index,
                            'count' => $wasteCount,
                        ]);
                }
                ?>

            </div>
        </div>
    </div>
    <?php endif; ?>
    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите произвольный комментарий']) ?>

    <div class="form-group">
        <?= $model->renderSubmitButtons() ?>

    </div>
    <?= $form->field($model, 'law_no')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'oos_number')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'revision')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'fo_ca_name')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'findTool')->hiddenInput()->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
function fetchLicenseRequests() {
    var data = $("#$formNameId-fo_ca_id").select2("data");
    ca_id = data[0].id;
    org_id = $("#$formNameId-org_id").val();
    if ((ca_id != "") && (ca_id != undefined) && (org_id != "") && (org_id != undefined)) {
        $.get("$urlFetchLicenseRequests?ca_id=" + ca_id + "&org_id=" + org_id, function(result) {
            if (result != false) {
                $("#block-lr").html(result);
            }
        });        
    }
} // fetchLicenseRequests()
JS
, yii\web\View::POS_BEGIN);

$this->registerJs(<<<JS
// Обработчик щелчка по инструментам для поиска (по номеру тендера или по реквизитам).
//
function findToolOnClick() {
     value = $(this).attr("data-value");
     if ((value != "") && (value != undefined)) {
         $("#$formNameId-findtool").val(value);
     }
} // findToolOnClick()

// Обработчик щелчка по кнопке "Найти тендер(ы)".
//
function findTenderOnClick() {
    if ($("#$formNameId-urlsource").val()) {
        var \$btn = $(this);
        \$btn.button("loading");

        $.post("$urlFindTenderByNumber", $("#frmTender").serialize(), function(response) {
            if (response.tp_id) {
                $("#tp_id").text(response.tpName);
                $("#$formNameId-tp_id").val(response.tp_id).trigger("change");
            }

            \$fieldCaName = $("#$formNameId-fo_ca_name");
            if (\$fieldCaName.length > 0) {
                \$fieldCaName.val(response.customer_name);
            }
            else {
                if (response.fo_ca_id) {
                    var newOption = new Option(response.customer_name, response.fo_ca_id, true, true);
                    $("#$formNameId-fo_ca_id").append(newOption).trigger("change");
                }
            }

            // номер закона
            if (response.law_no) $("#$formNameId-law_no").val(response.law_no);
    
            // номер закупки
            if (response.regNumber) $("#$formNameId-oos_number").val(response.regNumber);
    
            // способ размещения закупки
            if (response.tk_id) $("#$formNameId-tk_id").val(response.tk_id).trigger("change");
    
            // номер редакции извещения
            if (response.revision) $("#$formNameId-revision").val(response.revision);
    
            // наименование закупки
            if (response.name) $("#$formNameId-title").val(response.name);
    
            // НМЦ
            if (response.price) $("#$formNameId-amount_start").val(response.price);
    
            // размер обеспечения заявки
            if (response.amount_fo) $("#$formNameId-amount_fo").val(response.amount_fo);
    
            // размер обеспечения контракта
            if (response.amount_fc) $("#$formNameId-amount_fc").val(response.amount_fc);
    
            // дата размещения закупки
            if (response.placed_at_f) {
                $("#$formNameId-placed_at-disp").val(response.placed_at_f);
                $("#$formNameId-placed_at").val(response.placed_at_u);
            }
    
            // дата окончания приема заявок
            if (response.pf_date_u) {
                $("#$formNameId-date_stop-disp").val(response.pf_date_f);
                $("#$formNameId-date_stop").val(response.pf_date_u);
            }
    
            // дата подведения итогов
            if (response.su_date_u) {
                $("#$formNameId-date_sumup-disp").val(response.su_date_f);
                $("#$formNameId-date_sumup").val(response.su_date_u);
            }
    
            // дата проведения аукциона
            if (response.auction_at_u) {
                $("#$formNameId-date_auction-disp").val(response.auction_at_f);
                $("#$formNameId-date_auction").val(response.auction_at_u);
            }
        }).always(function() {
            \$btn.button("reset");
        });
    }

    return false;
} // findTenderOnClick()

// Обработчик щелчка по кнопке "Добавить" в табличной части "Отходы".
//
function btnAddNewWasteOnClick() {
    \$btn = $("#$btnAddWasteId");
    counter = parseInt(\$btn.attr("data-count"));
    next_counter = counter+1;
    $("#$wastePreloaderId").show();
    $.get("$urlAddWasteRow?counter=" + counter, function(data) {
        \$block = $("#$blockWasteId");
        if ($("div[id ^= '$wasteRowId-']").length == 0) \$block.html("");
        \$block.append(data);
        $("#$wastePreloaderId").hide();
        $("html, body").animate({ scrollTop: ($("#$wasteRowId-" + next_counter).offset().top - 78) }, 1000);
    });

    // наращиваем количество добавленных строк
    \$btn.attr("data-count", next_counter);

    return false;
} // btnAddNewWasteOnClick()

// Обработчик щелчка по кнопке "Удалить" в блоке "Отходы".
//
function btnDeleteNewWasteOnClick(event) {
    var counter = $(this).attr("data-counter");
    $("#$wasteRowId-" + counter).fadeOut(300, function() {
        $(this).remove();
        if ($("div[id ^= '$wasteRowId-']").length == 0) $("#$blockWasteId").html('$emptyWasteBlockPrompt');
    });

    return false;
} // btnDeleteNewWasteOnClick()

// Обработчик изменения значения в поле "Запрос лицензий".
//
function licenseRequestOnChange() {
    counter = parseInt($("#$btnAddWasteId").attr("data-count"));
    $("#$wastePreloaderId").show();
    $.get("$urlFillFkko?counter=" + counter + "&lr_id=" + $("#$formNameId-lr_id").val(), function (data) {
        if ($("div[id ^= '$wasteRowId-']").length == 0) $("#$blockWasteId").html("");
        $("#$blockWasteId").append(data);
        $("#$wastePreloaderId").hide();
        count = $("div[id ^= '$wasteRowId-']").last().attr("data-counter");
        alert("count: " + count);
        if (count) {
            $("#$btnAddWasteId").attr("data-count", count);
        }
    });
} // licenseRequestOnChange

$(document).on("click", "a[data-toggle ^= 'pill']", findToolOnClick);
$(document).on("click", "#findTender", findTenderOnClick);
$(document).on("change", "#$formNameId-org_id", fetchLicenseRequests);

$(document).on("click", "#$btnAddWasteId", btnAddNewWasteOnClick);
$(document).on("click", "a[id ^= '$btnDeleteWasteId']", btnDeleteNewWasteOnClick);
$(document).on("change", "#$formNameId-lr_id", licenseRequestOnChange);
JS
, yii\web\View::POS_READY);
?>
