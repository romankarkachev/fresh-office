<?php

use backend\controllers\DesktopWidgetsController;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\DesktopWidgetsAccess;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model common\models\DesktopWidgetsAccess */
/* @var $form yii\bootstrap\ActiveForm */

$formNameId = strtolower($model->formName());
$blockId = DesktopWidgetsAccess::DOM_IDS['BLOCK_ENTITY_ID'];
$labelId = DesktopWidgetsAccess::DOM_IDS['LABEL_ID'];
?>

<div class="widget-usage-form">
    <?php $form = ActiveForm::begin([
        'id' => DesktopWidgetsAccess::DOM_IDS['PJAX_FORM_ID'],
        'action' => DesktopWidgetsController::URL_ADD_USAGE_AS_ARRAY,
        'options' => ['data-pjax' => true],
    ]); ?>

    <div class="panel panel-success">
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-3 col-md-3 col-lg-2 col-xl-1">
                    <?= $form->field($model, 'type')->widget(Select2::class, [
                        'data' => DesktopWidgetsAccess::arrayMapOfTypesForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                        'pluginEvents' => [
                            'change' => 'function() {
    $block = $("#' . $blockId . '");
    $label = $("#' . $labelId . '");
    $label.append(\' &nbsp;<i id="preloader" class="fa fa-spinner fa-pulse fa-fw text-primary"></i><span class="sr-only">Подождите...</span>\');
    $block.load("' . Url::to(DesktopWidgetsController::URL_RENDER_ENTITY_FIELD_AS_ARRAY) . '?type=" + $(this).val(), function () {
        $block.show();
        $label.html("' . $model->getAttributeLabel('type') . '");
    });
}',
                        ],
                    ])->label($model->getAttributeLabel('type'), ['id' => $labelId]) ?>

                </div>
                <div id="<?= $blockId ?>" class="col-md-2 collapse"></div>
                <div class="col-md-1">
                    <label class="control-label btn-block">&nbsp;</label>
                    <?= Html::submitButton('Добавить <i class="fa fa-arrow-down"></i> ', ['class' => 'btn btn-success']) ?>

                </div>
            </div>
            <p class="text-muted text-justify">Обратите внимание, что данная форма &mdash; интерактивная, то есть изменения сохранять не нужно, все действия применяются на лету.</p>
        </div>
    </div>

    <?= $form->field($model, 'widget_id')->hiddenInput()->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
