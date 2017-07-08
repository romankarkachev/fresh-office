<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use common\models\TransportRequests;
use common\models\Regions;
use common\models\PeriodicityKinds;

/* @var $this yii\web\View */
/* @var $model common\models\TransportRequests */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $waste common\models\TransportRequestsWaste[] */
/* @var $transport common\models\TransportRequestsTransport[] */

$add_row_prompt = '<p class="text-muted">Табличная часть пуста.</p>';
?>

<div class="transport-requests-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'customer_id')->widget(Select2::className(), [
                'initValueText' => $model->customer_name,
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
                    'templateSelection' => new JsExpression('function (result) {
if (!result.custom) return result.text;
$("#transportrequests-customer_name").val(result.text);
return result.text;
}'),
                ],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'region_id')->widget(Select2::className(), [
                'data' => Regions::arrayMapOnlyRussiaForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'pluginEvents' => [
                    'change' => new JsExpression('function() { regionOnChange(); }'),
                ],
            ]) ?>

        </div>
        <div id="block-city"><?php if ($model->region_id != null) echo $this->render('_city_field', [
                'model' => $model,
                'form' => $form,
            ]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'address')->textInput(['maxlength' => true, 'placeholder' => 'Введите адрес']) ?>

        </div>
        <div class="col-md-2">
            <div class="form-group field-<?= $model->formName() ?>-state_id">
                <label class="control-label" for="<?= $model->formName() ?>-state_id"><?= $model->getAttributeLabel('state_id') ?></label>
                <p><?= $model->stateName ?></p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'our_loading')->widget(Select2::className(), [
                'data' => TransportRequests::arrayMapOfBooleanForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'periodicity_id')->widget(Select2::className(), [
                'data' => PeriodicityKinds::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'comment_manager')->textarea(['rows' => 3, 'placeholder' => 'Введите комментарий']) ?>

        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'special_conditions')->textarea(['rows' => 3, 'placeholder' => 'Введите особые условия']) ?>

        </div>
    </div>


    <?php
    if (!$model->isNewRecord) {
        $count = count($waste)-1;
    ?>
    <div class="panel panel-danger">
        <div class="panel-heading">
            Отходы <span id="waste-preloader" class="collapse"><i class="fa fa-cog fa-spin text-muted"></i></span>
            <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i> Добавить', '#', ['id' => 'btnAddFkkoRow', 'class' => 'btn btn-warning btn-xs pull-right', 'data-count' => $count, 'title' => 'Добавить строку']) ?>

        </div>
        <div class="panel-body">
            <?= $form->field($model, 'tpWasteErrors', ['template' => "{error}"])->staticControl() ?>

            <div id="block-tpWaste">
                <?php
                if (count($waste) == 0) echo $add_row_prompt;

                foreach ($waste as $index => $tpr)
                    echo $this->render('_row_fkko', [
                        'tr' => $model,
                        'model' => $tpr,
                        'counter' => $index,
                        'count' => $count
                    ]);
                ?>

            </div>
        </div>
    </div>
    <?php
        if (Yii::$app->user->can('root') || Yii::$app->user->can('logist')) {
            $count = count($transport)-1;
    ?>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h4>Транспорт <span id="transport-preloader" class="collapse"><i class="fa fa-cog fa-spin text-muted"></i></span>
                <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i> Добавить', '#', ['id' => 'btnAddTransportRow', 'class' => 'btn btn-success btn-xs pull-right', 'data-count' => $count, 'title' => 'Добавить строку']) ?>

            </h4>
        </div>
        <div class="panel-body">
            <?= $form->field($model, 'tpTransportErrors', ['template' => "{error}"])->staticControl() ?>

            <div id="block-tpTransport">
                <?php
                $collapse = ' class="collapse"';
                if (count($transport) == 0) echo $add_row_prompt;

                foreach ($transport as $index => $tpr) {
                    if ($tpr->tt->is_spec) $collapse = '';

                    echo $this->render('_row_transport', [
                        'tr' => $model,
                        'model' => $tpr,
                        'counter' => $index,
                        'count' => $count
                    ]);
                }
                ?>

            </div>
            <div id="block-spec"<?= $collapse ?>>
                <hr />
                <div class="panel panel-default">
                    <div class="panel-heading">Особые условия для спецтехники</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-2">
                                <?= $form->field($model, 'spec_free')->widget(Select2::className(), [
                                    'data' => TransportRequests::arrayMapOfBooleanForSelect2(),
                                    'theme' => Select2::THEME_BOOTSTRAP,
                                    'options' => ['placeholder' => '- выберите -'],
                                ]) ?>

                            </div>
                            <div class="col-md-2">
                                <?= $form->field($model, 'spec_hose', [
                                    'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub" aria-hidden="true"></i></span></div>{error}'
                                ])->widget(yii\widgets\MaskedInput::className(), [
                                    'clientOptions' => [
                                        'alias' => 'decimal',
                                        'digits' => 2,
                                        'digitsOptional' => true,
                                        'radixPoint' => '.',
                                        'groupSeparator' => '',
                                        'autoGroup' => true,
                                        'removeMaskOnSubmit' => true,
                                    ],
                                ])->textInput(['placeholder' => '0']) ?>

                            </div>
                        </div>
                        <?= $form->field($model, 'spec_cond')->textarea(['rows' => 3]) ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?= $form->field($model, 'comment_logist')->textarea(['rows' => 3, 'placeholder' => 'Введите комментарий']) ?>

    <?php }; ?>
    <?php }; ?>

    <?php if (Yii::$app->user->can('root') || Yii::$app->user->can('logist')): ?>
    <?= $form->field($model, 'closeRequest')->checkbox(['disabled' => $model->state_id == \common\models\TransportRequestsStates::STATE_ЗАКРЫТ])->label(null, ['style' => 'padding-left: 0px;']) ?>

    <?php endif; ?>
    <?= $form->field($model, 'customer_name')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Запросы на транспорт', ['/transport-requests'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$url_add_fkko = Url::to(['/transport-requests/render-fkko-row']);
$url_del_fkko = Url::to(['/transport-requests/delete-fkko-row']);

$url_add_transport = Url::to(['/transport-requests/render-transport-row']);
$url_del_transport = Url::to(['/transport-requests/delete-transport-row']);

$url_fields = Url::to(['/transport-requests/compose-region-fields']);
$this->registerJs(<<<JS
function regionOnChange() {
    $("#block-city").html("<p><i class=\"fa fa-cog fa-spin fa-2x text-muted\"></i><span class=\"sr-only\">Подождите...</span></p>");
    region_id = $("#transportrequests-region_id").val();
    if (region_id != 0 && region_id != "" && region_id != undefined)
        $("#block-city").load("$url_fields?region_id=" + region_id);
} // regionOnChange()

JS
, \yii\web\View::POS_BEGIN);

$this->registerJs(<<<JS
$("input").iCheck({
    checkboxClass: 'icheckbox_square-green',
});

// Обработчик щелчка по кнопке Добавить строку в табличной части Отходы.
//
function btnAddFkkoRowOnClick() {
    counter = parseInt($(this).attr("data-count"));
    next_counter = counter+1;
    $("#waste-preloader").show();
    $.get("$url_add_fkko?counter=" + counter, function(data) {
        if ($("div[id ^= 'fkko-row-']").length == 0) $("#block-tpWaste").html("");
        $("#block-tpWaste").append(data);
        $("#waste-preloader").hide();
    });

    // наращиваем количество добавленных строк
    $(this).attr("data-count", next_counter);

    return false;
} // btnAddFkkoRowOnClick()

// Обработчик щелчка по кнопке Удалить строку в табличной части Отходы.
//
function btnDeleteFkkoRowClick(event) {
    var message = "Удаление строки из табличной части производится сразу и безвозвратно. Продолжить?";
    var id = $(this).attr("data-id");
    var counter = $(this).attr("data-counter");

    // может быть, это просто новая строка
    // тогда просто ее удаляем и никаких post-запросов
    if (id == undefined) {
        $("#fkko-row-" + counter).remove();
        if ($("div[id ^= 'fkko-row-']").length == 0) $("#block-tpWaste").html('$add_row_prompt');
        return false;
    }

    if (confirm(message))
        $.ajax({
            type: "POST",
            url: "$url_del_fkko" + "?id=" + id,
            dataType: "json",
            async: false,
            success: function(result) {
                if (result == true) {
                    $("#fkko-row-" + counter).remove();
                    if ($("div[id ^= 'fkko-row-']").length == 0) $("#block-tpWaste").html('$add_row_prompt');
                }
            }
        });

    return false;
} // btnDeleteFkkoRowClick()

// Обработчик щелчка по кнопке Добавить строку в табличной части Транспорт.
//
function btnAddTransportRowOnClick() {
    counter = parseInt($(this).attr("data-count"));
    next_counter = counter+1;
    $("#transport-preloader").show();
    $.get("$url_add_transport?counter=" + counter, function(data) {
        if ($("div[id ^= 'transport-row-']").length == 0) $("#block-tpTransport").html("");
        $("#block-tpTransport").append(data);
        $("#transport-preloader").hide();
    });

    // наращиваем количество добавленных строк
    $(this).attr("data-count", next_counter);

    return false;
} // btnAddTransportRowOnClick()

// Обработчик щелчка по кнопке Удалить строку в табличной части Транспорт.
//
function btnDeleteTransportRowClick(event) {
    var message = "Удаление строки из табличной части производится сразу и безвозвратно. Продолжить?";
    var id = $(this).attr("data-id");
    var counter = $(this).attr("data-counter");

    // может быть, это просто новая строка
    // тогда просто ее удаляем и никаких post-запросов
    if (id == undefined) {
        $("#transport-row-" + counter).remove();
        if ($("div[id ^= 'transport-row-']").length == 0) $("#block-tpTransport").html('$add_row_prompt');
        return false;
    }

    if (confirm(message))
        $.ajax({
            type: "POST",
            url: "$url_del_transport" + "?id=" + id,
            dataType: "json",
            async: false,
            success: function(result) {
                if (result == true) {
                    $("#transport-row-" + counter).remove();
                    if ($("div[id ^= 'transport-row-']").length == 0) $("#block-tpTransport").html('$add_row_prompt');
                }
            }
        });

    return false;
} // btnDeleteTransportRowClick()

$(document).on("click", "#btnAddFkkoRow", btnAddFkkoRowOnClick);
$(document).on("click", "a[id ^= 'btnDeleteFkkoRow']", btnDeleteFkkoRowClick);

$(document).on("click", "#btnAddTransportRow", btnAddTransportRowOnClick);
$(document).on("click", "a[id ^= 'btnDeleteTransportRow']", btnDeleteTransportRowClick);
JS
, \yii\web\View::POS_READY);
?>
