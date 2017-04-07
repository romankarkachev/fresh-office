<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\Documents */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $tprows common\models\DocumentsTp[] */
/* @var $hkrows common\models\DocumentsHk[] */
/* @var $hks common\models\HandlingKinds[] */

$project_label = $model->attributeLabels()['fo_project'] . ' <span id="project-tp-preloader" class="collapse"><i class="fa fa-cog fa-spin text-warning"></i></span>';
?>

<div class="documents-form">
    <?php $form = ActiveForm::begin(); ?>

    <?php if (!$model->isNewRecord): ?>
    <p><strong>Автор:</strong> <?= $model->author->username ?><?= $model->authorProfile->name != null && $model->authorProfile->name != '' ? ' (' . $model->authorProfile->name . ')' : '' ?>. <strong>Создан</strong> <?= Yii::$app->formatter->asDate($model->created_at, 'php:d F Y в H:i') ?>.</p>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'doc_num')->textInput(['maxlength' => true, 'placeholder' => 'Введите номер акта']) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'doc_date')->widget(DateControl::className(), [
                'value' => $model->doc_date,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true
                    ],
                ],
            ]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'fo_project')->widget(Select2::className(), [
                'theme' => Select2::THEME_BOOTSTRAP,
                'language' => 'ru',
                'options' => ['placeholder' => 'Введите наименование'],
                'pluginOptions' => [
                    'minimumInputLength' => 1,
                    'language' => 'ru',
                    'ajax' => [
                        'url' => Url::to(['documents/direct-sql-get-project-data']),
                        'delay' => 500,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {project_id:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(result) { return result.text; }'),
                    'templateSelection' => new JsExpression('function (result) {
if (!result.customer_id) {return result.text;}

// подставим идентификатор контрагента в соответствующее поле
if (result.customer_id != "") $("#documents-fo_customer").val(result.customer_id);

// заполним договор
if (result.contract != "") $("#documents-fo_contract").val(result.contract);

return result.text;
}'),
                ],
                'pluginEvents' => [
                    'select2:select' => 'function() {
// заполним табличную часть позициями из проекта
counter = parseInt($("#btn-add-row").attr("data-count"));
$("#project-tp-preloader").show();
$.get("/documents/direct-sql-get-project-table-part?doc_id=1&project_id=" + $(this).val() + "&counter=" + counter, function(data) {
    if (data != false) {
        if (data.results != "") {
            if (counter == -1)
                $("#block-tp").after(data.results);
            else
                $("#dtp-row-" + counter).after(data.results);
        };

        for (var i = counter; i < data.counter; i++) {
            $("#documents-product_id-" + (i+1)).select2({theme: "default", width: "100%"});
        }
        $("#btn-add-row").attr("data-count", data.counter);
    }
    $("#project-tp-preloader").hide();
});
}',
                ]
            ])->label($project_label) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'fo_customer')->textInput(['maxlength' => true, 'placeholder' => 'Введите ID заказчика'])->label('ID заказчика') ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'fo_contract')->textInput(['maxlength' => true, 'placeholder' => 'Введите договор'])->label('Договор') ?>

        </div>
    </div>
    <div class="form-group">
        <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите примечание (не обязательно)']) ?>

    </div>
    <?= $form->field($model, 'author_id')->hiddenInput()->label(false) ?>

    <?php if (!$model->isNewRecord): ?>
    <?php $count = count($tprows)-1; ?>
    <div id="block-tp" class="page-header"><h3>Табличная часть <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i> Добавить строку', '#', ['id' => 'btn-add-row', 'class' => 'btn btn-default', 'data-count' => $count]) ?></h3></div>
    <?= $form->field($model, 'tp', ['template' => "{error}"])->staticControl() ?>

    <?php foreach ($tprows as $index => $tpr): ?>
    <?= $this->render('_tp', ['form' => $form, 'document' => $model, 'model' => $tpr, 'counter' => $index, 'count' => $count]) ?>
    <?php endforeach; ?>

    <?php endif; ?>
    <?php if (!$model->isNewRecord): ?>
    <div class="page-header"><h3>Виды обращения с отходами</h3></div>
    <?php
        foreach ($hks as $index => $hk)
            echo '
        <div class="checkbox">
            <label>
                ' . Html::input('checkbox', 'Documents[hks][' . $index . '][hk_id]', $hk->id, ['checked' => in_array($hk->id, $hkrows, false)]) . $hk->name . '
            </label>
        </div>';
    ?>
    <?php endif; ?>

    <p class="text-warning"><strong>Внимание!</strong> Экспорт применяется только для уже сохраненных данных. Если отображающиеся данные не сохранены, они не будут экспортированы.</p>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Документы', ['/documents'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

        <?= Html::a('<i class="fa fa-table" aria-hidden="true"></i> Экспорт', ['/documents/export', 'doc_id' => $model->id], ['class' => 'btn btn-default btn-lg']) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$url_add_row = Url::to(['/documents/render-row']);
$url_del_row = Url::to(['/documents/delete-row']);
$this->registerJs(<<<JS
// Обработчик щелчка по кнопке Добавить строку
//
function btnAddRowOnClick() {
    counter = parseInt($(this).attr("data-count"));
    next_counter = counter+1;
    $.get("$url_add_row?counter=" + counter, function(data) {
        if (counter == -1)
            $("#block-tp").after(data);
        else
            $("#dtp-row-" + counter).after(data);
        $("#documents-product_id-" + next_counter).select2({theme: "default", width: "100%"});
    });

    // наращиваем количество добавленных строк
    $(this).attr("data-count", next_counter);

    return false;
} // btnAddRowOnClick()

// Обработчик щелчка по кнопке Удалить строку
//
function btnDeleteRowClick(event) {
    var message = "Удаление строки из табличной части производится сразу и безвозвратно. Продолжить?";
    var id = $(this).attr("data-id");
    var counter = $(this).attr("data-counter");

    // может быть, это просто новая строка
    // тогда просто ее удаляем и никаких post-запросов
    if (id == undefined) {
        $("#dtp-row-" + counter).remove();
        // более не уменьшаем счетчик количества строк
        return false;
    }

    if (confirm(message))
        $.ajax({
            type: "POST",
            url: "$url_del_row" + "?id=" + id,
            dataType: "json",
            async: false,
            success: function(result) {
                if (result == true) {
                    $("#dtp-row-" + counter).remove();
                }
            }
        });

    return false;
} // btnDeleteRowClick()

$(document).on("click", "#btn-add-row", btnAddRowOnClick);
$(document).on("click", "a[id ^= 'btn-delete-row']", btnDeleteRowClick);
JS
, yii\web\View::POS_READY);
?>
