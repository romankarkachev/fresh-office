<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\Ferrymen;
use common\models\FerrymenTypes;
use common\models\PaymentConditions;
use common\models\Opfh;

/* @var $this yii\web\View */
/* @var $model common\models\Ferrymen */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dpDrivers common\models\Drivers[] */
/* @var $dpTransport common\models\Transport[] */
?>

<div class="ferrymen-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Введите наименование']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'state_id')->widget(Select2::className(), [
                'data' => Ferrymen::arrayMapOfStatesForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'ft_id')->widget(Select2::className(), [
                'data' => FerrymenTypes::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'pc_id')->widget(Select2::className(), [
                'data' => PaymentConditions::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'opfh_id')->widget(Select2::className(), [
                'data' => Opfh::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'tax_kind')->widget(Select2::className(), [
                'data' => Ferrymen::arrayMapOfTaxKindsForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12"><p class="lead">Диспетчер</p></div>
        <div class="col-md-2">
            <?= $form->field($model, 'contact_person')->textInput(['maxlength' => true, 'placeholder' => 'Введите имя', 'title' => 'Введите имя контактного лица']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'phone')->textInput(['maxlength' => true, 'placeholder' => 'Введите телефоны']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'Введите E-mail']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'post')->textInput(['maxlength' => true, 'placeholder' => 'Введите должность']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12"><p class="lead">Руководитель</p></div>
        <div class="col-md-2">
            <?= $form->field($model, 'contact_person_dir')->textInput(['maxlength' => true, 'placeholder' => 'Введите имя', 'title' => 'Введите имя контактного лица']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'phone_dir')->textInput(['maxlength' => true, 'placeholder' => 'Введите телефоны']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'email_dir')->textInput(['maxlength' => true, 'placeholder' => 'Введите E-mail']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'post_dir')->textInput(['maxlength' => true, 'placeholder' => 'Введите должность']) ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Перевозчики', ['/ferrymen'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
            <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>

        <?php else: ?>
            <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$opfhPhys = Opfh::OPFH_ФИЗЛИЦО;
$opfhIp = Opfh::OPFH_ИП;
$opfhOOO = Opfh::OPFH_ООО;

$this->registerJs(<<<JS
// Обработчик изменения значения в поле "Наименование".
//
function ferrymenNameOnChange() {
    name = $(this).val();
    if (name != "") {
        if (name.substr(0, 2).toLowerCase() == "ип")
            $("#ferrymen-opfh_id").val($opfhIp).trigger("change");
        else if (name.substr(0, 3).toLowerCase() == "ооо")
            $("#ferrymen-opfh_id").val($opfhOOO).trigger("change");
        else
            $("#ferrymen-opfh_id").val($opfhPhys).trigger("change");
    }
} // ferrymenNameOnChange()

$(document).on("change", "#ferrymen-name", ferrymenNameOnChange);
JS
, \yii\web\View::POS_READY);
?>
