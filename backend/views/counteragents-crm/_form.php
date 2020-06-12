<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use backend\controllers\CounteragentsCrmController;

/* @var $this yii\web\View */
/* @var $model common\models\foCompany */
/* @var $form yii\bootstrap\ActiveForm */

$formName = $model->formName();
$formNameId = strtolower($formName);

$valueNotIdentifiedPrompt = 'Значение из карточки, которое не удалось идентифицировать';

$lblRegion = $model->getAttributeLabel('REGION');
$lblCity = $model->getAttributeLabel('CITY');
$lblSource = $model->getAttributeLabel('INFORM_IN_COMPANY');
$lblPaymentMethod = $model->getAttributeLabel('ADD_forma_oplati');

$regionName = $model->REGION;
$cityName = $model->CITY;
$sourceName = $model->INFORM_IN_COMPANY;
$pmName = $model->ADD_forma_oplati;
?>

<div class="fo-company-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'COMPANY_NAME')->textInput(['autofocus' => true, 'placeholder' => 'Введите наименование']) ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'ADD_who')->widget(Select2::class, [
                'data' => \common\models\foOrganizations::arrayMapNamesForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'REGION')->widget(Select2::class, [
                'data' => \common\models\Regions::arrayMapOfRussiaRegionsNamesForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ])->label($lblRegion, ['id' => 'lblRegion']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'CITY')->widget(Select2::class, [
                'data' => \common\models\Cities::arrayMapCitiesOfRussiaByGroupsNamesForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ])->label($lblCity, ['id' => 'lblCity']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'INFORM_IN_COMPANY')->widget(Select2::class, [
                'data' => \common\models\foSources::arrayMapNamesForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ])->label($lblSource, ['id' => 'lblSource']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'ID_MANAGER')->widget(Select2::class, [
                'data' => \common\models\foManagers::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'ADD_forma_oplati')->widget(Select2::class, [
                'data' => \common\models\foPaymentMethods::arrayMapNamesForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ])->label($lblPaymentMethod, ['id' => 'lblPaymentMethod']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'ADD_days_post')->widget(\yii\widgets\MaskedInput::class, [
                'mask' => '999',
                'clientOptions' => ['placeholder' => ''],
            ])->textInput(['maxlength' => true, 'placeholder' => 'К-во дней', 'title' => 'Введите количество дней отсрочки при постоплате']) ?>

        </div>
    </div>
    <?= $form->field($model, 'DOP_INF')->textarea(['rows' => 6, 'placeholder' => 'Введите примечание']) ?>

    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . CounteragentsCrmController::MAIN_MENU_LABEL, CounteragentsCrmController::URL_ROOT_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
// если некоторые значения идентифицировать не удалось, выведем их в чистом виде в метку поля

if (!empty($regionName)) {
    $this->registerJs(<<<JS
// наименование региона
if ($("#$formNameId-region").val() == "") {
    $("#lblRegion").html("$lblRegion: <abbr class=\"text-muted\" title=\"$valueNotIdentifiedPrompt\">$regionName</abbr>");
}
JS
, yii\web\View::POS_READY);
}

if (!empty($cityName)) {
    $this->registerJs(<<<JS
// наименование города
if ($("#$formNameId-city").val() == "") {
    $("#lblCity").html("$lblCity: <abbr class=\"text-muted\" title=\"$valueNotIdentifiedPrompt\">$cityName</abbr>");
}
JS
, yii\web\View::POS_READY);
}

if (!empty($sourceName)) {
    $this->registerJs(<<<JS
// наименование источника
if ($("#$formNameId-inform_in_company").val() == "") {
    $("#lblSource").html("$lblSource: <abbr class=\"text-muted\" title=\"$valueNotIdentifiedPrompt\">$sourceName</abbr>");
}
JS
, yii\web\View::POS_READY);
}

if (!empty($pmName)) {
    $this->registerJs(<<<JS
// наименование формы оплаты
if ($("#$formNameId-add_forma_oplati").val() == "") {
    $("#lblPaymentMethod").html("$lblPaymentMethod: <abbr class=\"text-muted\" title=\"$valueNotIdentifiedPrompt\">$pmName</abbr>");
}
JS
, yii\web\View::POS_READY);
}
?>

