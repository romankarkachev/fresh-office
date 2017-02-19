<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model common\models\Documents */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $tprows common\models\DocumentsTp[] */
/* @var $hks common\models\DocumentsHk[] */
?>

<div class="documents-form">
    <?php $form = ActiveForm::begin(); ?>

    <?php if (!$model->isNewRecord): ?>
    <p><strong>Автор:</strong> <?= $model->author->username ?><?= $model->authorProfile->name != null && $model->authorProfile->name != '' ? ' (' . $model->authorProfile->name . ')' : '' ?>. <strong>Создан</strong> <?= Yii::$app->formatter->asDate($model->created_at, 'php:d F Y в H:i') ?>.</p>
    <?php endif; ?>

    <div class="row">
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
            <?= $form->field($model, 'fo_project')->textInput(['maxlength' => true, 'placeholder' => 'Введите ID проекта']) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'fo_customer')->textInput(['maxlength' => true, 'placeholder' => 'Введите ID заказчика']) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'fo_contract')->textInput(['maxlength' => true, 'placeholder' => 'Введите ID договора']) ?>

        </div>
    </div>
    <div class="form-group">
        <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите примечание (не обязательно)']) ?>

    </div>
    <?= $form->field($model, 'author_id')->hiddenInput()->label(false) ?>

    <?php if (!$model->isNewRecord): ?>
    <div class="page-header"><h3>Табличная часть</h3></div>
    <?php
        $count = count($tprows)-1;
        foreach ($tprows as $index => $tpr)
            echo $this->render('_tp', ['form' => $form, 'document' => $model, 'model' => $tpr, 'counter' => $index, 'count' => $count]);
    ?>
    <p class="text-warning"><strong>Внимание!</strong> Экспорт применяется только для уже сохраненных данных. Если отображающиеся данные не сохранены, они не будут экспортированы.</p>
    <?php endif; ?>

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
    var message = "Удаление автомобиля из табличной части производится безвозвратно. Продолжить?";
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
                    // более не уменьшаем счетчик количества строк
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
