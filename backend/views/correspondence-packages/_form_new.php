<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use common\models\ProjectsStates;
use common\models\User;
use common\models\TransportRequests;

/* @var $this yii\web\View */
/* @var $model common\models\CorrespondencePackages */
/* @var $form yii\bootstrap\ActiveForm */

$formNameId = strtolower($model->formName());
$urlFetchContactPersons = Url::to(['/correspondence-packages/fetch-contact-persons']);
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
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'fo_id_company')->widget(Select2::className(), [
                        'initValueText' => TransportRequests::getCustomerName($model->fo_id_company),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'language' => 'ru',
                        'options' => ['placeholder' => 'Введите наименование'],
                        'pluginOptions' => [
                            'minimumInputLength' => 1,
                            'language' => 'ru',
                            'ajax' => [
                                'url' => Url::to(['correspondence-packages/counteragent-casting-by-name']),
                                'delay' => 500,
                                'dataType' => 'json',
                                'data' => new JsExpression('function(params) { return {q:params.term}; }')
                            ],
                            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                            'templateResult' => new JsExpression('function(result) { return result.text; }'),
                            'templateSelection' => new JsExpression('function (result) {
if (!result.custom) return result.text;
$("#' . $formNameId . '-customer_name").val(result.text);
$("#' . $formNameId . '-manager_id").val(result.managerId).trigger("change");
$contactField = $("#' . $formNameId . '-fo_contact_id");
$.get("' . $urlFetchContactPersons . '?id=" + result.id, function (retval) {
    $contactField.empty().trigger("change");
    $.each(retval, function(index, value) {
        var newOption = new Option(value.text, value.id, true, true);
        $contactField.append(newOption).trigger("change");
    });
});
return result.text;
}'),
                        ],
                    ]) ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'manager_id')->widget(Select2::className(), [
                        'data' => User::arrayMapForSelect2(User::ARRAY_MAP_OF_USERS_BY_MANAGER_AND_ECOLOGIST_ROLE),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                    ]) ?>

                </div>
                <?php if (Yii::$app->user->can('root')): ?>
                <div class="col-md-3">
                    <?= $form->field($model, 'fo_contact_id')->widget(Select2::className(), [
                        'initValueText' => $model->contact_person != null ? $model->contact_person : '',
                        'data' => $model->fo_id_company != null ? $model->arrayMapOfContactPersonsForSelect2() : [],
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                    ]) ?>

                </div>
                <?php endif; ?>
            </div>
            <?= $form->field($model, 'other')->textarea(['rows' => 3, 'placeholder' => 'Введите наименования других документов из этого пакета']) ?>

            <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите примечание']) ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Пакеты корреспонденции', ['/correspondence-packages'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать черновик', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?= $form->field($model, 'customer_name')->hiddenInput()->label(false) ?>

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
$name = $model->formName() . '[tpPad]';
$stateSent = ProjectsStates::STATE_ОТПРАВЛЕНО;

$this->registerJs(<<<JS
var checked = false;
$("input").iCheck({checkboxClass: 'icheckbox_square-green'});

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

// Обработчик щелчка по ссылке "Отметить наиболее распространенные документы".
//
function checkRegularDocumentsOnClick() {
    var values = ["1", "2", "3" , "4"];
    $.each(values, function(index, value) {
        $("input[data-id = '" + value + "'").iCheck("check");
    });

    return false;
} // checkRegularDocumentsOnClick()

$(document).on("click", "#checkAllDocuments", checkAllDocumentsOnClick);
$(document).on("click", "#checkRegularDocuments", checkRegularDocumentsOnClick);
JS
, \yii\web\View::POS_READY);
?>
