<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use common\models\TransportRequests;
use common\models\UploadingFilesMeanings;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model common\models\FileStorage */
/* @var $form yii\bootstrap\ActiveForm */

$formName = strtolower($model->formName());
$labelProject = $model->attributeLabels()['project_id'];
$urlFindFolderByName = Url::to(['/storage/find-folder-by-name']);
?>

<div class="file-storage-form">
    <?php $form = ActiveForm::begin(['id' => 'frmStorage']); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'ca_id')->widget(Select2::className(), [
                'initValueText' => TransportRequests::getCustomerName($model->ca_id),
                'theme' => Select2::THEME_BOOTSTRAP,
                'language' => 'ru',
                'options' => ['placeholder' => 'Введите наименование'],
                'pluginOptions' => [
                    'minimumInputLength' => 3,
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
    if (!result.id) {return result.text;}
    $("#' . $formName . '-ca_name" ).val(result.text);
    return result.text;
}'),
                ],
                'pluginEvents' => [
                    'change' => new JsExpression('function() {
    var data = $(this).select2("data");
    id = data[0].id;
    name = data[0].text;
    if (id != "" && id != undefined && name != "" && name != undefined) {
        $.get("' . $urlFindFolderByName . '?id=" + id + "&name=" + name, function(result) {
            if (result != false) {
                $("#block-folder").html(result);
            }
        });        
    }
}'),
                ],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'type_id')->widget(Select2::className(), [
                'data' => UploadingFilesMeanings::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div id="block-project" class="collapse">
            <div class="col-md-2">
                <?= $form->field($model, 'project_id')->widget(MaskedInput::className(), [
                    'mask' => '99999',
                    'clientOptions' => ['placeholder' => ''],
                ])->textInput(['maxlength' => true, 'placeholder' => 'ID проекта'])->label($labelProject, ['id' => 'lblProject']) ?>

            </div>
            <div class="col-md-1">
                <label for="<?= $formName ?>-is_scan" class="control-label">Скан</label>
                <?= $form->field($model, 'is_scan')->checkbox()->label(false) ?>

            </div>
        </div>
        <?php if ($model->isNewRecord): ?>
        <div class="col-md-2">
            <?= $form->field($model, 'file[]')->fileInput(['multiple' => true]) ?>

        </div>
        <?php endif; ?>
        <?= $form->field($model, 'ca_name')->hiddenInput()->label(false) ?>

    </div>
    <div id="block-folder"></div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Файловое хранилище', ['/storage'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$urlFindFolderByName = Url::to(['/storage/find-folder-by-name']);
$urlProjectOnChange = Url::to(['/storage/project-on-change']);

$this->registerJs(<<<JS
$("input[type='checkbox']").iCheck({checkboxClass: "icheckbox_square-green"});

// Обработчик изменения значения в поле "Контрагент".
//
function caOnChange() {
    var data = $(this).select2("data");
    //$("#$formName-name").val(data[0].text);
    /*
    ca = $("input[name='FileStorage[ca_id]']:checked").val();
    alert(ca);return;
    */
    id = data[0].id;
    name = data[0].text;
    if (id != "" && id != undefined && name != "" && name != undefined) {
        $.get("$urlFindFolderByName?id=" + id + "&name=" + name, function(result) {
            if (result != false) {
                $("#block-folder").html(result);
            }
        });        
    }
} // caOnChange()

// Обработчик изменения значения в поле "Тип контента".
//
function typeOnChange() {
    if ($(this).val() == "2") {
        $("#block-project").show();
    }
    else {
        $("#$formName-project_id").val("");
        $("#block-project").hide();
    }
} // typeOnChange()

// Обработчик изменения значения в поле "Проект".
//
function projectOnChange() {
    project_id = $("#$formName-project_id").val();
    if ((project_id != "") && (project_id != undefined)) {
        \$label = $("#lblProject");
        \$label.html("$labelProject &nbsp;<i class=\"fa fa-spinner fa-pulse fa-fw text-primary\"></i>");
        $.get("$urlProjectOnChange?project_id=" + project_id, function(response) {
            if (response != false) {
                var newOption = new Option(response.name, response.id, true, true);
                $("#$formName-ca_id").append(newOption).trigger("change");
            }
        }).always(function() {
            \$label.html("$labelProject");
        });;
    }
} // projectOnChange()

$("#frmStorage").keydown(function(event) {
    if (event.keyCode == 13) {
        event.preventDefault();
        return false;
    }
});

/*
$("#$formName-project_id").on("keypress", function (e) {
     if (e.which === 13) {
        projectOnChange();
     }
});
*/

$(document).on("change", "#$formName-type_id", typeOnChange);
$(document).on("change", "#$formName-project_id", projectOnChange);
JS
, \yii\web\View::POS_READY);
?>
