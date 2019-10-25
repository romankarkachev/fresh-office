<?php

use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\MaskedInput;
use backend\controllers\PoController;
use common\models\PoEi;
use common\models\Po;

/* @var $this yii\web\View */
/* @var $model common\models\Po */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dpProperties array свойства и значения свойств статьи расходов платежного ордера */

$promptEmptyProperties = '<p class="text-muted">' . Po::PROMPT_EMPTY_PROPERTIES . '</p>';
$formName = strtolower($model->formName());
$blockProjectId = 'block-fo_project_id';
if (!isset($propertiesBlock)) $propertiesBlock = '';
?>

<div class="po-form">
    <?php $form = ActiveForm::begin(); ?>

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
                'pluginEvents' => [
                    'select2:select' => new JsExpression('function() {
    eiOnChange();
}'),
                ],
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
        <div id="<?= $blockProjectId ?>" class="col-md-2 collapse">
            <?= $form->field($model, 'fo_project_id')->widget(MaskedInput::className(), [
                'mask' => '99999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['maxlength' => true, 'placeholder' => 'ID проекта', 'title' => 'Введите ID проекта из CRM Fresh Office']) ?>

        </div>
    </div>
    <div id="block-properties" class="form-group"><?php if (!empty($model->ei_id)) echo $this->render('_properties_block', ['properties' => $dpProperties]) ?></div>
    <div class="form-group">
        <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите комментарий']) ?>

    </div>
    <div class="form-group">
        <?= $model->renderSubmitButtons() ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$eiGroupTransportID = \common\models\PoEig::ГРУППА_ТРАНСПОРТ;
$eiTransportID = \common\models\PoEi::СТАТЬЯ_ПЕРЕВОЗЧИКИ;
$urlRenderProperties = Url::to(PoController::URL_RENDER_PROPERTIES_AS_ARRAY);
$urlDeleteValueLink = Url::to(PoController::URL_DELETE_VALUE_LINK_AS_ARRAY);

$this->registerJs(<<<JS
// Обработчик изменения значения в поле "Статья расходов".
//
function eiOnChange() {
    \$blockProject = $("#$blockProjectId");
    \$fieldProject = $("#$formName-fo_project_id");

    \$blockProject.hide();
    \$fieldProject.removeAttr("required");

    ei_id = $("#$formName-ei_id").val();
    if ((ei_id != "") && (ei_id != undefined)) {
        if (ei_id == $eiTransportID) {
            // если пользователь выбрал статью из группы "Транспорт", то необходимо показать поле "ID проекта"
            \$blockProject.show();
            \$fieldProject.attr("required", "required");
        }

        url = "$urlRenderProperties?ei_id=" + ei_id;
        \$block = $("#block-properties");
        \$block.html('<p class="text-center"><i class="fa fa-spinner fa-pulse fa-fw text-primary text-muted"></i><span class="sr-only">Подождите...</span></p>');
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
