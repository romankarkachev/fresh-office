<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\web\View;
use yii\widgets\MaskedInput;
use backend\controllers\CompaniesController;

/* @var $this yii\web\View */
/* @var $model common\models\Companies */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="companies-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="form-group">
        <?= Html::input('text', $model->formName() . '[dadataCasting]', null, [
            'id' => 'dadataCasting',
            'class' => 'form-control',
            'placeholder' => 'Мастер подбора контрагентов',
            'title' => 'Универсальный подбор и автозаполнение реквизитов контрагентов',
            'autofocus' => true,
        ]) ?>

    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'inn')->widget(MaskedInput::class, [
                'mask' => '999999999999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите ИНН']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'kpp')->widget(MaskedInput::class, [
                'mask' => '999999999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите КПП']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'ogrn')->widget(MaskedInput::class, [
                'mask' => '999999999999999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите ОГРН или ОГРНИП', 'title' => 'Введите ОГРН или ОГРНИП']) ?>

        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Введите внутреннее наименование', 'title' => 'Например, Ромашка']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'name_full')->textInput(['maxlength' => true, 'placeholder' => 'Введите полное наименование', 'title' => 'Например, Общество с ограниченной ответственностью "Ромашка"']) ?>

        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'name_short')->textInput(['maxlength' => true, 'placeholder' => 'Введите сокращенное наименование', 'title' => 'Например, ООО "Ромашка"']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'address_j')->textInput(['maxlength' => true, 'placeholder' => 'Введите юридический адрес']) ?>

        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'address_f')->textInput(['maxlength' => true, 'placeholder' => 'Введите фактический адрес']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'dir_post')->textInput(['maxlength' => true, 'placeholder' => 'Введите должность', 'title' => $model->getAttributeLabel('dir_post')])->label('Директор должность') ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'dir_name')->textInput(['maxlength' => true, 'placeholder' => 'Введите ФИО', 'title' => $model->getAttributeLabel('dir_name')])->label('Директор ФИО') ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'dir_name_of')->textInput(['maxlength' => true, 'placeholder' => 'Введите ФИО в род. падеже', 'title' => $model->getAttributeLabel('dir_name_of')])->label('Директор ФИО род.') ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'dir_name_short')->textInput(['maxlength' => true, 'placeholder' => 'Введите сокращ. ФИО', 'title' => $model->getAttributeLabel('dir_name_short')])->label('Директор ФИО сокр.') ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'dir_name_short_of')->textInput(['maxlength' => true, 'placeholder' => 'Введите сокращ. ФИО в род. падеже', 'title' => $model->getAttributeLabel('dir_name_short_of')])->label('Директор ФИО сокр. род.') ?>

        </div>
    </div>
    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите произвольный комментарий']) ?>

    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . CompaniesController::ROOT_LABEL, CompaniesController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$formName = strtolower($model->formName());
$token = \common\models\DadataAPI::API_TOKEN;

$this->registerCssFile('https://cdn.jsdelivr.net/npm/suggestions-jquery@19.4.2/dist/css/suggestions.min.css');

$this->registerJsFile('https://cdn.jsdelivr.net/npm/suggestions-jquery@19.4.2/dist/js/jquery.suggestions.min.js', ['depends' => 'yii\web\JqueryAsset', 'position' => View::POS_END]);

$this->registerJs(<<<JS
$("#dadataCasting").suggestions({
    token: "$token",
    type: "PARTY",
    onSelect: function(suggestion) {
        $("#$formName-inn").val("");
        $("#$formName-kpp").val("");
        $("#$formName-ogrn").val("");
        $("#$formName-name").val("");
        $("#$formName-name_full").val("");
        $("#$formName-name_short").val("");
        $("#$formName-address_j").val("");
        $("#$formName-dir_name").val("");
        $("#$formName-dir_post").val("");

        if (suggestion.data.state.liquidation_date != null)  {
            if (!confirm("Предприятие ликвидировано! Вы действительно хотите продолжить?")) {
                return true;
            }
        }

        if (suggestion.data.inn) $("#$formName-inn").val(suggestion.data.inn);
        if (suggestion.data.kpp) $("#$formName-kpp").val(suggestion.data.kpp);
        if (suggestion.data.ogrn) $("#$formName-ogrn").val(suggestion.data.ogrn);
        if (suggestion.data.name.full) $("#$formName-name").val(suggestion.data.name.full);
        if (suggestion.data.name.full_with_opf) $("#$formName-name_full").val(suggestion.data.name.full_with_opf);
        if (suggestion.data.name.short_with_opf) $("#$formName-name_short").val(suggestion.data.name.short_with_opf);
        if (suggestion.data.address.value) $("#$formName-address_j").val(suggestion.data.address.value);
        if (suggestion.data.management) {
            // для ИП таких реквизитов нет
            if (suggestion.data.management.name) $("#$formName-dir_name").val(suggestion.data.management.name);
            if (suggestion.data.management.post) $("#$formName-dir_post").val(suggestion.data.management.post);
        }
    }
});
JS
, View::POS_READY);
?>
