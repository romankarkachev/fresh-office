<?php

use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\MaskedInput;
use common\models\Po;
use common\models\PoEi;
use common\models\PoEig;
use common\models\PaymentOrdersStates;
use common\models\User;
use backend\controllers\PoController;

/* @var $this yii\web\View */
/* @var $model common\models\Po */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dpProperties array свойства и значения свойств статьи расходов платежного ордера */

$promptEmptyProperties = '<p class="text-muted">' . Po::PROMPT_EMPTY_PROPERTIES . '</p>';
$blockAdditionalFieldId = 'block-af';
if (!isset($propertiesBlock)) $propertiesBlock = '';

$formNameId = strtolower($model->formName());
?>

<div class="po-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->errorSummary($model) ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'company_id')->widget(Select2::class, [
                'initValueText' => $model->companyName,
                'theme' => Select2::THEME_BOOTSTRAP,
                'language' => 'ru',
                'options' => ['placeholder' => 'Введите наименование (ИНН, ОГРН)'],
                'pluginOptions' => [
                    'minimumInputLength' => 1,
                    'language' => 'ru',
                    'ajax' => [
                        'url' => Url::to(\backend\controllers\CompaniesController::URL_CASTING_AS_ARRAY),
                        'delay' => 500,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(result) { return result.text; }'),
                    'templateSelection' => new JsExpression('function (result) { return result.text; }'),
                ],
            ]) ?>

        </div>
        <div class="col-md-5">
            <?= $form->field($model, 'ei_id')->widget(Select2::class, [
                'data' => PoEi::arrayMapByGroupsForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'pluginEvents' => ['select2:select' => new JsExpression('function() { eiOnChange(); }')],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'amount', [
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
            ]) ?>

        </div>
        <?php if ((Yii::$app->user->can('root') || Yii::$app->user->can('accountant') || Yii::$app->user->can('accountant_b')) && ($model->state_id == PaymentOrdersStates::PAYMENT_STATE_АВАНСОВЫЙ_ОТЧЕТ)): ?>
        <div class="col-md-2">
            <?= $form->field($model, 'paid_at')->widget(DateControl::className(), [
                'value' => $model->paid_at,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:U',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => [
                        'placeholder' => 'Выберите дату оплаты',
                        'autocomplete' => 'off',
                    ],
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                    'pluginEvents' => [
                        'changeDate' => 'function(e) { anyDateOnChange(); }',
                    ],
                ],
            ]) ?>

        </div>
        <?php endif; ?>
        <div id="<?= $blockAdditionalFieldId ?>" class="col-md-2">
            <?php
            if (!$model->isNewRecord && !empty($model->ei)) {
                $viewName = '';

                $group_id = $model->ei->group_id;
                if ($model->ei_id == PoEi::СТАТЬЯ_БЛАГОДАРНОСТИ) {
                    $viewName = '_field_ca';
                }
                elseif ($group_id == PoEig::ГРУППА_ТРАНСПОРТ && $model->ei_id == PoEi::СТАТЬЯ_ПЕРЕВОЗЧИКИ) {
                    $viewName = '_field_project';
                }
                elseif ($group_id == PoEig::ГРУППА_ЭКОЛОГИЯ) {
                    $viewName = '_field_ep';
                }

                if (!empty($viewName)) echo $this->render($viewName, ['model' => $model, 'form' => $form]);
            }
            ?>
        </div>
    </div>
    <div id="block-properties" class="form-group"><?php if (!empty($model->ei_id)) echo $this->render('_properties_block', ['properties' => $dpProperties]) ?></div>
    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите комментарий']) ?>

    <?php if (Yii::$app->user->can('root')): ?>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'created_by')->widget(Select2::class, [
                'data' => User::arrayMapForSelect2(User::USERS_FO_ATTACHED),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
    </div>
    <?php else: ?>
    <?= $form->field($model, 'created_by', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>

    <?php endif; ?>
    <div class="form-group">
        <?= $model->renderSubmitButtons() ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$urlRenderAdditionalField = Url::to(PoController::URL_RENDER_AF_BLOCK_AS_ARRAY);

$urlRenderProperties = Url::to(PoController::URL_RENDER_PROPERTIES_AS_ARRAY);
$urlDeleteValueLink = Url::to(PoController::URL_DELETE_VALUE_LINK_AS_ARRAY);

if ($model->state_id == PaymentOrdersStates::PAYMENT_STATE_АВАНСОВЫЙ_ОТЧЕТ) {
    $btnARApproveId = Po::DOM_IDS['btnApproveAdvanceReportId'];
    $btnARRejectId = Po::DOM_IDS['btnRejectAdvanceReportId'];
    $btnARApprovePrompt = Po::DOM_IDS['btnApproveAdvanceReportLabel'];
    $btnARRejectPrompt = Po::DOM_IDS['btnRejectAdvanceReportLabel'];

    $this->registerJs(<<<JS
// Функция-обработчик изменения даты в любом из соответствующих полей.
//
function anyDateOnChange() {
    var elements = {"$btnARApproveId": '$btnARApprovePrompt', "$btnARRejectId": '$btnARRejectPrompt'};

    $.each(elements, function(id) {
        var button = $("#" + id);
        button.attr("disabled", "disabled");
        button.text("Подождите...");
    });

    setTimeout(function () {
        $.each(elements, function(id, prompt) {
            var button = $("#" + id);
            button.removeAttr("disabled");
            button.html(prompt);
        });
    }, 1500);
}
JS
    , \yii\web\View::POS_BEGIN);
}

$this->registerJs(<<<JS
// Обработчик изменения значения в поле "Статья расходов".
//
function eiOnChange() {
    ei_id = $("#$formNameId-ei_id").val();
    if (ei_id) {
        \$blockAf = $("#$blockAdditionalFieldId");
        \$blockAf.html('<i class="fa fa-spinner fa-pulse fa-fw text-primary text-muted"></i><span class="sr-only">Подождите...</span>');
        \$blockAf.load("$urlRenderAdditionalField?ei_id=" + ei_id, function() {
            // можно сделать заполнение появляющегося поля обязательным
            //\$fieldProject.attr("required", "required");
        });

        url = "$urlRenderProperties?ei_id=" + ei_id;
        \$block = $("#block-properties");
        \$block.html('<i class="fa fa-spinner fa-pulse fa-fw text-primary text-muted"></i><span class="sr-only">Подождите...</span>');
        \$block.load(url);
    }
} // eiOnChange()

// Обработчик щелчка по кнопке "Удалить значение свойства".
//
function btnDeleteValueLinkOnClick(event) {
    var id = $(this).attr("data-id");
    if (confirm("Будет произведено удаление привязки данного значения к выбранной статье расходов. Продолжить?")) {
        $.ajax({
            type: "POST",
            url: "$urlDeleteValueLink" + "?id=" + id,
            dataType: "json",
            async: false,
            success: function(result) {
                if (result == true) {
                    $("#link-row-" + id).remove();
                    if ($("div[id ^= 'link-row-']").length == 0) $("#block-properties").html('$promptEmptyProperties');
                }
            }
        });
    }

    return false;
} // btnDeleteValueLinkOnClick()

$(document).on("click", "a[id ^= 'btnDeleteValueLink']", btnDeleteValueLinkOnClick);
JS
, \yii\web\View::POS_READY);
?>
