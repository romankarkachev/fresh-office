<?php

use backend\controllers\EcoContractsController;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\MaskedInput;
use common\models\EcoMcTp;
use common\models\TransportRequests;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\EcoMc */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $newReportModel common\models\EcoMcTp */

$emptyReportsBlockPrompt = '<p class="card-text text-muted">Нет добавленных отчетов.</p>';

if ($model->crudeReports instanceof ActiveDataProvider) {
    $reportsCount = $model->crudeReports->getTotalCount();
}
elseif (is_array($model->crudeReports))
    $reportsCount = count($model->crudeReports);
else
    $reportsCount = 0;

$urlAddReportRow = Url::to(EcoContractsController::URL_RENDER_REPORT_ROW_AS_ARRAY);

$blockReportsId = EcoMcTp::DOM_IDS['BLOCK_ID'];
$reportRowId = EcoMcTp::DOM_IDS['ROW_ID'];
$reportPreloaderId = EcoMcTp::DOM_IDS['PRELOADER'];
$btnAddReportId = EcoMcTp::DOM_IDS['ADD_BUTTON'];
$btnDeleteReportId = EcoMcTp::DOM_IDS['DELETE_BUTTON'];
?>

<div class="eco-mc-form">
    <?php if ($model->isNewRecord): ?>
    <?php $form = ActiveForm::begin(); ?>

    <?php endif; ?>
    <div class="row">
        <div class="col-md-6">
            <?php if (!$model->isNewRecord): ?>
            <?php $form = ActiveForm::begin(); ?>

            <?php endif; ?>
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'org_id')->widget(Select2::className(), [
                        'data' => \common\models\Organizations::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'fo_ca_id')->widget(Select2::class, [
                        'initValueText' => TransportRequests::getCustomerName($model->fo_ca_id),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'language' => 'ru',
                        'options' => ['placeholder' => 'Введите наименование'],
                        'pluginOptions' => [
                            'allowClear' => true,
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
                    ]) ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'amount', [
                        'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
                    ])->widget(MaskedInput::class, [
                        'clientOptions' => [
                            'alias' =>  'numeric',
                            'digits' => false,
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
            </div>
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'manager_id')->widget(Select2::class, [
                        'data' => User::arrayMapForSelect2(User::ARRAY_MAP_OF_USERS_BY_MANAGER_AND_ECOLOGIST_ROLE),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('Ответственный') ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'date_start')->widget(DateControl::class, [
                        'value' => $model->date_start,
                        'type' => DateControl::FORMAT_DATE,
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'layout' => '{input}{picker}',
                            'options' => [
                                'placeholder' => 'выберите дату начала',
                                'title' => 'Выберите дату начала действия договора',
                                'autocomplete' => 'off',
                            ],
                            'pluginOptions' => [
                                'weekStart' => 1,
                                'autoclose' => true,
                            ],
                        ],
                    ])->label('Действует с') ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'date_finish')->widget(DateControl::class, [
                        'value' => $model->date_finish,
                        'type' => DateControl::FORMAT_DATE,
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'layout' => '{input}{picker}',
                            'options' => [
                                'placeholder' => 'выберите дату окончания',
                                'title' => 'Выберите дату окончания действия договора',
                                'autocomplete' => 'off',
                            ],
                            'pluginOptions' => [
                                'weekStart' => 1,
                                'autoclose' => true,
                            ],
                        ],
                    ])->label('Действует по') ?>

                </div>
            </div>
            <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите комментарий']) ?>

            <div class="form-group">
                <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . EcoContractsController::ROOT_LABEL, EcoContractsController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

                <?= $model->renderSubmitButtons() ?>

            </div>
            <?php if (!$model->isNewRecord): ?>
            <?php ActiveForm::end(); ?>

            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <strong class="card-title lead">Регламентированные отчеты</strong>
                    <?php if ($model->isNewRecord): ?><span id="<?= $reportPreloaderId ?>" class="collapse"><i class="fa fa-cog fa-spin text-muted"></i></span>
                    <?= Html::button('<i class="fa fa-plus"></i> Добавить', [
                        'id' => EcoMcTp::DOM_IDS['ADD_BUTTON'],
                        'class' => 'btn btn-default btn-xs pull-right',
                        'data-count' => $reportsCount,
                    ]) ?>

                    <?php endif; ?>
                </div>
                <div class="panel-body">
                    <div id="<?= $blockReportsId ?>">
                        <?php
                        if (!$model->isNewRecord) {
                            echo $this->render('_reports_list', ['model' => $newReportModel, 'dataProvider' => $model->crudeReports]);
                        }
                        else {
                            if ($reportsCount == 0) {
                                echo $emptyReportsBlockPrompt;
                            }
                            else {
                                foreach ($model->crudeReports as $index => $row)
                                    echo $this->render('_row_report_fields', [
                                        'model' => $row,
                                        'parentModel' => $model,
                                        'form' => $form,
                                        'counter' => $index,
                                        'count' => $reportsCount,
                                    ]);
                            }
                        }
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if ($model->isNewRecord): ?>
    <?php ActiveForm::end(); ?>

    <?php endif; ?>
</div>
<?php
$this->registerJs(<<<JS

// Обработчик щелчка по кнопке "Добавить отчет в договор" в блоке "Регламентированные отчеты".
//
function btnAddNewReportOnClick() {
    \$btnDropdown = $("#$btnAddReportId");
    counter = parseInt(\$btnDropdown.attr("data-count"));
    next_counter = counter+1;
    $("#$reportPreloaderId").show();
    $.get("$urlAddReportRow?counter=" + counter, function(data) {
        \$block = $("#$blockReportsId");
        if ($("div[id ^= '$reportRowId-']").length == 0) \$block.html("");
        \$block.append(data);
        $("#$reportPreloaderId").hide();
        $("html, body").animate({ scrollTop: ($("#$reportRowId-" + next_counter).offset().top - 78) }, 1000);
    });

    // наращиваем количество добавленных строк
    \$btnDropdown.attr("data-count", next_counter);

    return false;
} // btnAddNewReportOnClick()

// Обработчик щелчка по кнопке "Удалить отчет" в блоке "Регламентированные отчеты".
//
function btnDeleteNewReportOnClick(event) {
    var counter = $(this).attr("data-counter");
    $("#$reportRowId-" + counter).fadeOut(300, function() {
        $(this).remove();
        if ($("div[id ^= '$reportRowId-']").length == 0) $("#$blockReportsId").html('$emptyReportsBlockPrompt');
    });

    return false;
} // btnDeleteNewReportOnClick()

$(document).on("click", "#$btnAddReportId", btnAddNewReportOnClick);
$(document).on("click", "a[id ^= '$btnDeleteReportId']", btnDeleteNewReportOnClick);
JS
, yii\web\View::POS_READY);
?>
