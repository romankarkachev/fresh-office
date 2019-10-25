<?php

use backend\controllers\PoPropertiesController;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use common\models\PoValues;
use common\models\PoEip;

/* @var $this yii\web\View */
/* @var $model common\models\PoProperties */
/* @var $newValueModel common\models\PoValues */
/* @var $newEPModel common\models\PoEip */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . PoPropertiesController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = PoPropertiesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;

$frmNewValueId = PoValues::DOM_IDS['PJAX_FORM_ID'];
$frmNewEPId = PoEip::DOM_IDS['PJAX_FORM_ID'];
?>
<div class="po-properties-update">
    <div class="po-properties-form form-group">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Введите наименование']) ?>

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
    <div class="row">
        <div class="col-md-6">
            <?= $this->render('/po-values/_values_list', ['model' => $newValueModel, 'dataProvider' => $model->values]) ?>

        </div>
        <div class="col-md-6">
            <?= $this->render('/po-ei/_items_list', ['model' => $newEPModel, 'dataProvider' => $model->ei]) ?>

        </div>
    </div>
</div>
<?php
$urlRenameValue = Url::to(PoPropertiesController::URL_RENAME_VALUE_AS_ARRAY);

$this->registerJs(<<<JS

// Обработчик щелчка по ссылкам для переименования значений свойств.
//
function renameValueOnClick() {
    id = $(this).attr("data-id");
    if (id != "" && id != undefined) {
        $("#fieldRenameValue" + id).show();
        $("#renameValue" + id).hide();
    }

    return false;
} // renameValueOnClick()

// Обработчик изменения наименования значения свойства.
//
function fieldRenameValueOnChange() {
    id = $(this).attr("data-id");
    name = $(this).val();
    $.post("$urlRenameValue", {id: id, name: name}, function(response) {
        \$label = $("#renameValue" + id);
        if (response == true) {
            \$label.text(name);
        }
        $("#fieldRenameValue" + id).hide();
        \$label.show();
    });
} // fieldRenameValueOnChange()

$(document).on("click", "a[id ^= 'renameValue']", renameValueOnClick);
$(document).on("change", "input[id ^= 'fieldRenameValue']", fieldRenameValueOnChange);
JS
, yii\web\View::POS_READY);
?>
