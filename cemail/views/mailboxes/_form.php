<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\CEMailboxesTypes;
use common\models\CEMailboxesCategories;

/* @var $this yii\web\View */
/* @var $model common\models\CEMailboxes */
/* @var $form yii\bootstrap\ActiveForm */

$formName = strtolower($model->formName());
?>

<div class="cemailboxes-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="card">
        <div class="card-block">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Введите логин', 'title' => 'Логин (имя пользователя) часто полностью совпадает с названием ящика']) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Введите наименование']) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'host')->textInput(['maxlength' => true, 'placeholder' => 'Введите хост']) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true, 'placeholder' => 'Введите пароль']) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'port')->textInput(['maxlength' => true, 'placeholder' => 'Введите порт']) ?>

                </div>
                <div class="col-auto">
                    <div class="form-group field-cemailboxes-is_active required has-danger">
                        <label for="<?= $formName ?>-is_active" class="control-label">Активен</label>
                        <?= $form->field($model, 'is_active')->checkbox(['title' => 'Сбор писем производится только по активным почтовым ящикам'])->label(false) ?>

                    </div>
                </div>
                <div class="col-auto">
                    <div class="form-group field-cemailboxes-is_ssl required has-danger">
                        <label for="<?= $formName ?>-is_ssl" class="control-label">SSL</label>
                        <?= $form->field($model, 'is_ssl')->checkbox()->label(false) ?>

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'type_id')->widget(Select2::className(), [
                        'data' => CEMailboxesTypes::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                    ]); ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'category_id')->widget(Select2::className(), [
                        'data' => CEMailboxesCategories::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]); ?>

                </div>
            </div>
        </div>
        <div class="card-footer text-muted">
            <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Почтовые ящики', ['/mailboxes'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

            <?php if ($model->isNewRecord): ?>
            <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
            <?php else: ?>
            <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
            <?php endif; ?>

        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
$("input").iCheck({checkboxClass: "icheckbox_square-green"});

function stripos (f_haystack, f_needle, f_offset) {
    var haystack = f_haystack.toLowerCase();
    var needle = f_needle.toLowerCase();
    var index = 0;
    if(f_offset == undefined) {
        f_offset = 0;
    }
    if ((index = haystack.indexOf(needle, f_offset)) > -1) {
        return index;
    }

    return false;
}

// Обработчик изменения значения в поле "Имя пользователя".
//
function usernameOnChange() {
    username = $(this).val();
    if (stripos(username, "@yandex.ru")) {
        $("#$formName-name").val(username.toLowerCase());
        $("#$formName-host").val("imap.yandex.ru");
        $("#$formName-port").val(993);
        $("#$formName-is_ssl").iCheck("check");
        $("#$formName-password").focus();
    }

    if (stripos(username, "@mail.ru")) {
        $("#$formName-name").val(username.toLowerCase());
        $("#$formName-host").val("imap.mail.ru");
        $("#$formName-port").val(993);
        $("#$formName-is_ssl").iCheck("check");
        $("#$formName-password").focus();
    }
} // usernameOnChange()

$(document).on("change", "#$formName-username", usernameOnChange);
JS
, yii\web\View::POS_READY);
?>