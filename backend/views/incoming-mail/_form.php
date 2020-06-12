<?php

use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use backend\controllers\IncomingMailController;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\IncomingMail */
/* @var $form yii\bootstrap\ActiveForm */

$formName = $model->formName();
$formNameId = strtolower($model->formName());
$labelOrgId = 'label-org_id';
$labelOrgPrompt = 'Организация';
?>

<div class="incoming-mail-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-5">
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'org_id')->widget(Select2::class, [
                        'data' => \common\models\Organizations::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -', 'title' => 'Получатель письма (юрлицо)'],
                        'hideSearch' => true,
                    ])->label($labelOrgPrompt, ['id' => $labelOrgId]) ?>

                </div>
                <div id="block-inc_num">
                <?php if (!empty($model->org_id)): ?>
                    <?= $this->render('_field_inc_num', ['model' => $model, 'form' => $form]) ?>

                <?php else: ?>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Вх. №</label>
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
                                'title' => 'Введите дату получения корреспонденции',
                            ],
                            'pluginOptions' => [
                                'weekStart' => 1,
                                'autoclose' => true,
                            ],
                        ],
                    ]) ?>

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
            <?= $form->field($model, 'date_complete_before')->widget(DateControl::class, [
                'value' => $model->date_complete_before,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => [
                        'placeholder' => ' -выберите дату -',
                        'autocomplete' => 'off',
                        'title' => 'Введите дату, последний день исполнения по письму',
                    ],
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'receiver_id')->widget(Select2::class, [
                'data' => User::arrayMapForSelect2(User::USERS_ALL_WEB_APP),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -', 'title' => $model->getAttributeLabel('receiver_id')],
            ])->label('Получатель') ?>

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
                        'url' => Url::to(IncomingMailController::URL_CASTING_COUNTERAGENT_MULTI),
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
    </div>
    <?= $form->field($model, 'description')->textarea(['rows' => 3, 'placeholder' => 'Введите описание документов во вложении']) ?>

    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите произвольный комментарий']) ?>

    <?= $form->field($model, 'direction', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'ca_src', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'ca_id', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'ca_name', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>


    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . IncomingMailController::ROOT_LABEL, IncomingMailController::URL_ROOT_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$urlRenderFieldIncNum = Url::to(IncomingMailController::URL_RENDER_FIELD_INC_NUM_AS_ARRAY);

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

$(document).on("change", "#$formNameId-org_id", orgOnChange);
JS
, \yii\web\View::POS_READY);
?>
