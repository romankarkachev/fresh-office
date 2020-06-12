<?php

use kartik\select2\Select2;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use common\models\TenderFormsVarieties;
use backend\controllers\TendersController;

/* @var $this yii\web\View */
/* @var $model common\models\TenderParticipantForms */

$labelVarietyPrompt = 'Выберите набор форм';
$labelVarietyId = 'lblVariety';
?>

<?php $form = ActiveForm::begin(['action' => Url::to(TendersController::URL_GENERATE_TENDER_FORMS_AS_ARRAY)]); ?>

<div class="row">
    <div class="col-md-2">
        <?= $form->field($model, 'variety_id')->widget(Select2::class, [
            'data' => TenderFormsVarieties::arrayMapForSelect2(),
            'theme' => Select2::THEME_BOOTSTRAP,
            'size' => Select2::SMALL,
            'hideSearch' => true,
            'options' => ['placeholder' => '- выберите -'],
            'pluginEvents' => [
                'change' => new JsExpression('function() { varietyOnChange($(this)); }'),
            ],
        ])->label($labelVarietyPrompt, ['id' => $labelVarietyId]) ?>

    </div>
</div>
<div id="block-fields" class="form-group"></div>

<?= $form->field($model, 'tender_id', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>

<?= Html::submitButton('Сгенерировать', ['class' => 'btn btn-info btn-lg']) ?>

<?php ActiveForm::end(); ?>

<?php
$urlRenderForms = Url::to(TendersController::URL_RENDER_FORMS_AS_ARRAY);

$this->registerJs(<<<JS

function varietyOnChange(element) {
    var data = element.select2("data");
    id = data[0].id;
    if (id) {
        \$label = $("#$labelVarietyId");
        \$label.html("$labelVarietyPrompt &nbsp;<i class=\"fa fa-spinner fa-pulse fa-fw text-primary\"></i>");
        $("#block-fields").load("$urlRenderForms?id=" + id, function () {
            \$label.html("$labelVarietyPrompt");
        });
    }
} // varietyOnChange()
JS
, yii\web\View::POS_BEGIN);
