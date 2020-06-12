<?php

use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use backend\controllers\IncomingMailController;
use backend\controllers\OutcomingMailController;
use common\models\User;
use common\models\PostDeliveryKinds;

/* @var $this yii\web\View */
/* @var $model common\models\IncomingMail */
/* @var $form yii\bootstrap\ActiveForm */

$formName = $model->formName();
$formNameId = strtolower($model->formName());
$labelOrgId = 'label-org_id';
$labelOrgPrompt = 'Организация';

$stateName = '';
if (!empty($model->state_id)) {
    $stateName = ' <span class="text-muted"><em><small>' . $model->stateName . '</small></em></span>';
}
?>

<div class="outcoming-mail-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-5">
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'org_id')->widget(Select2::class, [
                        'data' => \common\models\Organizations::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -', 'title' => 'Отправитель письма (юрлицо)'],
                        'hideSearch' => true,
                    ])->label($labelOrgPrompt, ['id' => $labelOrgId]) ?>

                </div>
                <div id="block-inc_num">
                <?php if (!empty($model->org_id)): ?>
                    <?= $this->render('_field_inc_num', ['model' => $model, 'form' => $form]) ?>

                <?php else: ?>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Исх. №</label>
                            <p class="form-control" title="Выберите организацию, номер будет рассчитан автоматически">?</p>
                        </div>
                    </div>
                <?php endif; ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'inc_date')->widget(DateControl::class, [
                        'value' => $model->inc_date,
                        'type' => DateControl::FORMAT_DATE,
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'layout' => '{input}{picker}',
                            'options' => [
                                'placeholder' => ' -выберите дату -',
                                'autocomplete' => 'off',
                                'title' => 'Введите дату отправки корреспонденции',
                            ],
                            'pluginOptions' => [
                                'weekStart' => 1,
                                'autoclose' => true,
                            ],
                        ],
                    ])->label('Исходящая дата') ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'type_id')->widget(Select2::class, [
                        'data' => \common\models\IncomingMailTypes::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                    ]) ?>

                </div>
            </div>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'receiver_id')->widget(Select2::class, [
                'data' => User::arrayMapForSelect2(User::USERS_ALL_WEB_APP),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -', 'title' => $model->getAttributeLabel('receiver_id')],
            ])->label('Отправитель') ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'counteragent')->widget(Select2::class, [
                'initValueText' => $model->ca_name,
                'theme' => Select2::THEME_BOOTSTRAP,
                'language' => 'ru',
                'options' => ['placeholder' => 'Введите наименование контрагента'],
                'pluginOptions' => [
                    'minimumInputLength' => 3,
                    'language' => 'ru',
                    'ajax' => [
                        'url' => Url::to(IncomingMailController::URL_CASTING_COUNTERAGENT_MULTI_AS_ARRAY),
                        'delay' => 500,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(result) { return result.text; }'),
                    'templateSelection' => new JsExpression('function (result) {
if (!result.id) { return result.text; }
$("#' . $formNameId . '-ca_name").val(result.text);
$("#' . $formNameId . '-ca_id").val(result.id);
if (result.src != undefined) $("#' . $formNameId . '-ca_src").val(result.src);
return result.text;
}'),
                ],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'track_num', ['template' => \common\models\CorrespondencePackages::FORM_FIELD_TRACK_TEMPLATE])
                ->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Введите идентификатор отправления',
                    'title' => 'Введите идентификатор отправления',
                ])->label($model->getAttributeLabel('track_num') . $stateName) ?>

        </div>
    </div>
    <?= $form->field($model, 'description')->textarea(['rows' => 3, 'placeholder' => 'Введите описание документов во вложении']) ?>

    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите произвольный комментарий']) ?>

    <?= $form->field($model, 'direction', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'ca_src', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'ca_id', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'ca_name', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>


    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . OutcomingMailController::ROOT_LABEL, OutcomingMailController::URL_ROOT_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<div id="modalWindow" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header"><h4 id="mwTitle" class="modal-title">Modal title</h4></div>
            <div id="mwBody" class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<?php
$pdPochtaRu = PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ;
$urlRenderFieldIncNum = Url::to(OutcomingMailController::URL_RENDER_FIELD_INC_NUM_AS_ARRAY);
$urlTrackByNumber = Url::to(['/tracking/track-by-number']);

$this->registerJs(<<<JS

// Обработчик изменения значения в поле "Организация".
//
function orgOnChange() {
    org_id = $(this).val();
    if (org_id) {
        \$label = $("#$labelOrgId");
        \$label.html("$labelOrgPrompt &nbsp;<i class=\"fa fa-spinner fa-pulse fa-fw text-primary\"></i>");

        url = "$urlRenderFieldIncNum?org_id=" + org_id;
        \$block = $("#block-inc_num");
        \$block.html("");
        \$block.load(url, function () {
            \$label.html("$labelOrgPrompt");
        });
    }
} // orgOnChange()

// Обработчик щелчка по кнопке "Отследить".
//
function btnTrackNumberOnClick() {
    tracknum = $("#$formNameId-track_num").val();
    if (tracknum) {
        $("#mwTitle").text("Трекинг");
        \$body = $("#mwBody");
        \$body.html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#modalWindow").modal();
        \$body.load("$urlTrackByNumber?pd_id=$pdPochtaRu&track_num=" + tracknum);
    }

    return false;
} // btnTrackNumberOnClick()

$(document).on("change", "#$formNameId-org_id", orgOnChange);
$(document).on("click", "#btnTrackNumber", btnTrackNumberOnClick);
JS
, \yii\web\View::POS_READY);
?>
