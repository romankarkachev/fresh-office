<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use backend\controllers\StorageTtnRequiredController;

/* @var $this yii\web\View */
/* @var $model common\models\StorageTtnRequired */
/* @var $form yii\bootstrap\ActiveForm */

$formName = strtolower($model->formName());
$labelTypeTitle = $model->attributeLabels()['type'];
$labelTypeId = 'lblType';
$urlRenderEntityBlock = Url::to(StorageTtnRequiredController::URL_RENDER_ENTITY_BLOCK_AS_ARRAY);
?>

<div class="storage-ttn-required-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'type')->widget(Select2::className(), [
                'data' => \common\models\StorageTtnRequired::arrayMapOfTypesForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ])->label($labelTypeTitle, ['id' => $labelTypeId]) ?>

        </div>
        <div class="col-md-3" id="block-entity">
        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . StorageTtnRequiredController::ROOT_LABEL, StorageTtnRequiredController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?= $model->renderSubmitButtons() ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
function typeOnChange() {
    \$block = $("#block-entity");
    type = $("#$formName-type").val();
    if ((type != "") && (type != undefined)) {
        \$label = $("#$labelTypeId");
        \$label.html("$labelTypeTitle &nbsp;<i class=\"fa fa-spinner fa-pulse fa-fw text-primary\"></i>");
        \$block.html("");
        $.get("$urlRenderEntityBlock?type=" + type, function(response) {
            if (response != false) {
                \$block.html(response);
            }
        }).always(function() {
            \$label.html("$labelTypeTitle");
        });
    }
} // typeOnChange()

$(document).on("change", "#$formName-type", typeOnChange);
JS
, yii\web\View::POS_READY);
?>
