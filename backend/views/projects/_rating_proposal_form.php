<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model common\models\CustomerRatingProposalForm */
/* @var $form yii\bootstrap\ActiveForm */

$this->title = 'Отправка клиенту предложения поставить оценку по проекту | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Отправка письма для оценки';

$formNameId = strtolower($model->formName());
$labelProject = $model->attributeLabels()['project_id'];
$labelProjectId = 'label-project';
?>
<div class="packing-types-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'project_id')->widget(MaskedInput::className(), [
                'mask' => '99999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => 'ID проекта',
                'autocomplete' => 'off',
            ])->label($labelProject, ['id' => $labelProjectId])?>

        </div>
        <div id="block-receiver" class="col-md-10"><?php if (!empty($model->errors)): ?><?= $this->render('_fields_crpf', ['form' => $form, 'model' => $model]) ?><?php endif;?></div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-paper-plane-o" aria-hidden="true"></i> Отправить', ['class' => 'btn btn-info btn-lg']) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$url = Url::to(['/projects/render-rating-proposal-fields']);
$urlFoContactOnChange = Url::to(['/correspondence-packages/fetch-contact-emails']);

$this->registerJs(<<<JS
// Обработчик изменения значения в поле "Проект".
//
function projectOnChange() {
    \$block = $("#block-receiver");
    \$block.html("");
    project_id = $(this).val();
    if ((project_id != "") && (project_id != undefined)) {
        \$label = $("#$labelProjectId");
        \$label.html("$labelProject &nbsp;<i class=\"fa fa-spinner fa-pulse fa-fw text-primary\"></i>");
        $.get("$url?project_id=" + project_id, function(response) {
            \$block.html(response);
        }).always(function() {
            \$label.html("$labelProject");
        });
    }
} // projectOnChange()

// Обработчик изменения значения в поле "Контактное лицо".
//
function contactPersonOnChange() {
    company_id = $("#$formNameId-ca_id").val();
    contact_id = $("#$formNameId-cp_id").val();
    $.get("$urlFoContactOnChange?company_id=" + company_id + "&contact_id=" + contact_id, function(retval) {
        $("#$formNameId-email").val(retval);
    });
} // contactPersonOnChange()

$(document).on("change", "#$formNameId-project_id", projectOnChange);
$(document).on("change", "#$formNameId-cp_id", contactPersonOnChange);
JS
, yii\web\View::POS_READY);
?>
