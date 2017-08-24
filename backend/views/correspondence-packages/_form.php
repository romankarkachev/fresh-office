<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\ProjectsStates;
use common\models\PostDeliveryKinds;

/* @var $this yii\web\View */
/* @var $model common\models\CorrespondencePackages */
/* @var $form yii\bootstrap\ActiveForm */

$inputGroupTemplate = "{label}\n<div class=\"input-group\">\n{input}\n<span class=\"input-group-btn\"><button class=\"btn btn-default\" type=\"button\" id=\"btnTrackNumber\"><i class=\"fa fa-search\" aria-hidden=\"true\"></i> Отследить</button></span></div>\n{error}";
?>

<div class="correspondence-packages-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-5">
            <?php
            $pad = json_decode($model->pad, true);
            if (is_array($pad))
                echo $this->render('_pad', [
                    'model' => $model,
                    'pad' => $pad,
                ]);
            ?>
        </div>
        <div class="col-md-7">
            <div class="panel panel-success">
                <div class="panel-heading">Проект</div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'fo_project_id')->textInput(['disabled' => true]) ?>

                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'customer_name')->textInput(['disabled' => true]) ?>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'stateName')->textInput(['disabled' => true]) ?>

                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'typeName')->textInput(['disabled' => true]) ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'state_id')->widget(Select2::className(), [
                'data' => ProjectsStates::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'pd_id')->widget(Select2::className(), [
                'data' => PostDeliveryKinds::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'track_num', ['template' => $inputGroupTemplate])
                ->textInput([
                    'maxlength' => true,
                    //'disabled' => $model->track_num != null,
                    'placeholder' => 'Введите идентификатор отправления',
                    'title' => 'Введите идентификатор отправления',
                ]) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'other')->textarea(['rows' => 3, 'placeholder' => 'Введите наименования других документов из этого пакета']) ?>

        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите примечание']) ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Пакеты корреспонденции', ['/correspondence-packages'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<div id="mw_summary" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="modal_title" class="modal-title">Modal title</h4>
            </div>
            <div id="modal_body" class="modal-body">
                <p>One fine body…</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<?php
$url = \yii\helpers\Url::to(['/tracking/pochta-ru']);
$name = $model->formName() . '[tpPad]';
$stateSent = ProjectsStates::STATE_ОТПРАВЛЕНО;

$this->registerJs(<<<JS
var checked = false;
$("input").iCheck({
    checkboxClass: 'icheckbox_square-green',
});

// Обработчик щелчка по ссылке "Отметить все документы".
//
function checkAllDocumentsOnClick() {
    if (checked) {
        operation = "uncheck";
        checked = false;
    }
    else {
        operation = "check";
        checked = true;
    }

    $("input[name ^= '$name']").iCheck(operation);

    return false;
} // checkAllDocumentsOnClick()

// Обработчик щелчка по кнопке "Отследить".
//
function btnTrackNumberOnClick() {
    tracknum = $("#correspondencepackages-track_num").val();
    if (tracknum != "" && tracknum != undefined) {
        $("#modal_title").text("Трекинг");
        $("#modal_body").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#mw_summary").modal();
        $("#modal_body").load("$url?track_num=" + tracknum);
    }

    return false;
} // btnTrackNumberOnClick()

// Обработчик изменения значения в поле "Трек-номер".
//
function trackNumberOnChange() {
    $("#correspondencepackages-state_id").val("$stateSent").trigger("change");
}

$(document).on("click", "#checkAllDocuments", checkAllDocumentsOnClick);
$(document).on("click", "#btnTrackNumber", btnTrackNumberOnClick);
$(document).on("change", "#correspondencepackages-track_num", trackNumberOnChange);
JS
, \yii\web\View::POS_READY);
?>
