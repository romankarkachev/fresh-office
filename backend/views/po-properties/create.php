<?php

use backend\controllers\PoPropertiesController;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\PoProperties */

$this->title = 'Новое свойство статей расходов | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\PoPropertiesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';

$addRowPrompt = '<p class="text-muted">Значений нет.</p>';
?>
<div class="po-properties-create">
    <div class="po-properties-form">
        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Введите наименование']) ?>

            </div>
            <div class="col-md-9">
                <?= $form->field($model, 'ei')->widget(Select2::className(), [
                    'data' => \common\models\PoEi::arrayMapByGroupsForSelect2(),
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'options' => ['placeholder' => '- выберите -', 'multiple' => true, 'title' => 'Выберите статьи, которые могут быть описаны данным свойством'],
                ]) ?>

            </div>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h4 style="margin:0px;">Допустимые значения <span id="values-preloader" class="collapse"><i class="fa fa-cog fa-spin text-muted"></i></span> <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i> Добавить', '#', [
                            'id' => 'btnAddValueRow',
                            'class' => 'btn btn-default btn-xs pull-right',
                            'title' => 'Добавить значение',
                            'data-count' => 0,
                        ]) ?></h4>
                    </div>
                    <div id="block-values" class="panel-body">
                        <?php
                        if (empty($model->values)) {
                            echo $addRowPrompt;
                        }
                        else {
                            foreach ($model->values as $index => $row)
                                echo $this->render('_row_value', [
                                    'property' => $model,
                                    'model' => $row,
                                    'counter' => $index,
                                    'form' => $form,
                                ]);
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . PoPropertiesController::ROOT_LABEL, PoPropertiesController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

            <?php if ($model->isNewRecord): ?>
            <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
            <?php else: ?>
            <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
            <?php endif; ?>

        </div>
        <?php ActiveForm::end(); ?>

    </div>
</div>
<?php
$urlAddValueRow = Url::to(PoPropertiesController::URL_RENDER_VALUE_ROW_AS_ARRAY);

$this->registerJs(<<<JS
// Обработчик щелчка по кнопке "Добавить" в табличной части "Значения свойства".
//
function btnAddValueRowOnClick() {
    counter = parseInt($(this).attr("data-count"));
    next_counter = counter+1;
    $("#values-preloader").show();
    $.get("$urlAddValueRow?counter=" + counter, function(data) {
        if ($("div[id ^= 'value-row-']").length == 0) $("#block-values").html("");
        $("#block-values").append(data);
        $("#values-preloader").hide();
    });

    // наращиваем количество добавленных строк
    $(this).attr("data-count", next_counter);

    return false;
} // btnAddValueRowOnClick()

// Обработчик щелчка по кнопке "Удалить строку" в табличной части "Значения свойства".
//
function btnDeleteValueRowOnClick(event) {
    var message = "Удаление строки из табличной части производится сразу и безвозвратно. Продолжить?";
    var id = $(this).attr("data-id");
    var counter = $(this).attr("data-counter");

    // может быть, это просто новая строка
    // тогда просто ее удаляем и никаких post-запросов
    if (id == undefined) {
        $("#value-row-" + counter).remove();
        if ($("div[id ^= 'value-row-']").length == 0) $("#block-values").html('$addRowPrompt');
        return false;
    }
} // btnDeleteValueRowOnClick

$(document).on("click", "#btnAddValueRow", btnAddValueRowOnClick);
$(document).on("click", "a[id ^= 'btnDeleteValueRow']", btnDeleteValueRowOnClick);
JS
, \yii\web\View::POS_READY);
