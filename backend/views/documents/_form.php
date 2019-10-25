<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use common\models\Edf;

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
    <p>
        <strong>Автор:</strong> <?= $model->createdByProfileName ?>.
        <strong>Создан</strong> <?= Yii::$app->formatter->asDate($model->created_at, 'php:d F Y в H:i') ?>.
        <strong>Организация:</strong> <?= $model->organizationName ?>.
        <strong>Контрагент:</strong> <?= $model->counteragentName ?>.
    </p>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'doc_num')->textInput(['maxlength' => true, 'placeholder' => 'Введите номер акта']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'doc_date')->widget(DateControl::class, [
                'value' => $model->doc_date,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => ['placeholder' => '- выберите -', 'autocomplete' => 'off'],
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'act_date')->widget(DateControl::class, [
                'value' => $model->act_date,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => ['placeholder' => '- выберите -', 'autocomplete' => 'off'],
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'ed_id')->widget(Select2::class, [
                'data' => Edf::arrayMapOfContractsForSelect2($model->fo_customer),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <div class="form-group">
        <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите примечание (не обязательно)']) ?>

    </div>
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
