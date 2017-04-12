<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use common\models\Appeals;

/* @var $this yii\web\View */
/* @var $model common\models\Appeals */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $is_wizard bool|null */
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
            <?php if ($model->fo_id_company === null): ?>
            <p><?= Html::button('Идентифицировать контрагента', ['id' => 'btn-identify-ca', 'class' => 'btn btn-default', 'title' => 'Попытаться идентифицировать контрагента', 'data-model-id' => $model->id, 'data-loading-text' => '<i class="fa fa-cog fa-spin fa-lg text-info"></i> Поиск по базе данных...', 'autocomplete' => 'off']) ?></p>
            <?php endif; ?>
            <div id="block-ca"><?= $this->render('_ca', ['model' => $model, 'form' => $form]) ?></div>
        </div>
    </div>
    <?php if (!isset($is_wizard)): ?>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Обращения', ['/appeals'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->state_id != Appeals::APPEAL_STATE_CLOSED): ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

        <?php endif; ?>
    </div>
    <?php else: if ($model->fo_id_company != null): ?>
        <div class="alert alert-success" role="alert">
            <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
            <strong>Изменения сохранены. Обновите страницу, чтобы перейти к следующему обращению.</strong>
        </div>
    <?php endif;endif; ?>
    <?php ActiveForm::end(); ?>

</div>
<?php
$url_id_ca = Url::to(['/appeals/try-to-identify-counteragent']);
$url_after_id = Url::to(['/appeals/after-identifying-ambiguous']);
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
    var \$btn = $("#btn-identify-ca");
    // идентификатор обращения
    appeal_id = \$btn.attr("data-model-id");
    // идентификатор выбранного контрагент
    ca_id = $(this).attr("data-caId");

    $("#block-ca").load("$url_after_id?appeal_id=" + appeal_id + "&ca_id=" + ca_id, function( response, status, xhr ) {
        if (status == "error") {
            \$btn.removeClass().addClass("btn btn-danger");
            $("#block-ca").html("Невозможно загрузить данные. Ошибка " + xhr.status + ": " + xhr.statusText + ".");
            return;
        }
    });

    \$btn.removeClass().addClass("btn btn-success");

    // скроллим кверху
    $("html, body").animate({scrollTop: 0}, 1000);

    return false;
} // tableMultipleRowOnClick()

$(document).on("click", "#btn-identify-ca", btnIdentifyOnClick);
$(document).on("click", "#select-row", tableMultipleRowOnClick);
JS
, \yii\web\View::POS_READY);
?>
