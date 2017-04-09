<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Appeals */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="appeals-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-5">
            <div class="panel panel-success">
                <div class="panel-heading">Форма обращения</div>
                <div class="panel-body">
                    <p><strong>Создано</strong>: <?= Yii::$app->formatter->asDate($model->created_at, 'php:d.m.Y в H:i') ?></p>
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'form_company')->textInput(['disabled' => true]) ?>

                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'form_username')->textInput(['disabled' => true]) ?>

                        </div>
                    </div>
                    <?= $form->field($model, 'form_region')->textInput(['disabled' => true]) ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'form_phone')->textInput(['disabled' => true]) ?>

                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'form_email')->textInput(['disabled' => true]) ?>

                        </div>
                    </div>
                    <?= $form->field($model, 'form_message')->textarea(['rows' => 6, 'disabled' => true]) ?>

                </div>
            </div>
        </div>
        <div class="col-md-7">
            <p><?= Html::button('Идентифицировать контрагента', ['id' => 'btn-identify-ca', 'class' => 'btn btn-default', 'title' => 'Попытаться идентифицировать контрагента', 'data-model-id' => $model->id, 'data-loading-text' => '<i class="fa fa-cog fa-spin fa-lg text-info"></i> Поиск по базе данных...', 'autocomplete' => 'off']) ?></p>
            <div id="block-ca"><?= $this->render('_ca', ['model' => $model, 'form' => $form]) ?></div>
        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Обращения', ['/appeals'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$url_id_ca = Url::to(['/appeals/try-to-identify-counteragent']);
$this->registerJs(<<<JS
// Функция выполняет попытку идентификации контрагента по имеющимся контактным данным.
// В случае успеха заполняютя поля Наименование и Идентификатор.
//
function btnIdentifyOnClick() {
    var \$btn = $(this);

    // делаем кнопку обычной
    \$btn.removeClass().addClass("btn btn-default");

    // включаем индикацию на кнопке (preloader)
    \$btn.button("loading");

    // фиксируем идентификатор обращения
    appeal_id = \$btn.attr("data-model-id");

    $("#block-ca").load("$url_id_ca?id=" + appeal_id, function( response, status, xhr ) {
        \$btn.button("reset");
        if (status == "error") {
            \$btn.removeClass().addClass("btn btn-danger");
            $("#block-ca").html("Невозможно загрузить данные. Ошибка " + xhr.status + ": " + xhr.statusText + ".");
            return;
        }

        if ($("#appeals-fo_id_company").val() != "") {
            // идентификация прошла успешно
            \$btn.removeClass().addClass("btn btn-success");
        }
    });

    return false;
} // btnIdentifyOnClick()

// Функция-обработчик щелчка по ссылке в таблице с множеством совпадений при идентификации контрагента.
//
function tableMultipleRowOnClick() {
    // заполняем поля
    // идентификатор контрагента
    $("#appeals-fo_id_company").val($(this).attr("data-caId"));
    $("#lbl-company-id").text($(this).attr("data-caId"));
    // наименование контрагента
    $("#appeals-fo_company_name").val($(this).attr("data-caName"));
    $("#lbl-company-name").text($(this).attr("data-caName"));
    // статус контрагента (в зависимости от финансов)
    $("#appeals-ca_state_id").val($(this).attr("data-stateId")).trigger("change");
    $("#lbl-state-name").text($(this).attr("data-stateName"));
    // ответственный
    $("#appeals-fo_id_manager").val($(this).attr("data-managerId")).trigger("change");

    $("#table-multiple").remove();
    $("#block-ca-hidden").show("fast");

    $("#btn-identify-ca").removeClass().addClass("btn btn-success");

    // скроллим кверху
    $("html, body").animate({scrollTop: 0}, 1000);

    return false;
} // tableMultipleRowOnClick()

$(document).on("click", "#btn-identify-ca", btnIdentifyOnClick);
$(document).on("click", "#select-row", tableMultipleRowOnClick);
JS
, \yii\web\View::POS_READY);
?>
