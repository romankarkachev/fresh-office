<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\EdfFillFkkoBasisForm;

/* @var $this yii\web\View */
/* @var $model common\models\EdfFillFkkoBasisForm */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $tr array массив запросов на транспорт */
/* @var $lr array массив запросов лицензий */

$sources = EdfFillFkkoBasisForm::arrayMapOfSourcesForSelect2();
$srcTr = EdfFillFkkoBasisForm::SRC_TR;
$srcLr = EdfFillFkkoBasisForm::SRC_LR;
$srcExcel = EdfFillFkkoBasisForm::SRC_EXCEL;
?>

<div class="fill-fkko-form">
    <?php $form = ActiveForm::begin([
        'action' => \backend\controllers\EdfController::FILL_FKKO_TR_BASIS_AS_ARRAY,
        'options' => ['id' => 'frmFillFkko'],
    ]); ?>

    <?= $form->field($model, 'src', [
        'inline' => true,
    ])->radioList($sources, [
        'class' => 'btn-group',
        'data-toggle' => 'buttons',
        'unselect' => null,
        'item' => function ($index, $label, $name, $checked, $value) use ($sources) {
            $hint = '';
            $key = array_search($value, array_column($sources, 'id'));
            if ($key !== false && isset($sources[$key]['hint'])) $hint = ' title="' . $sources[$key]['hint'] . '"';

            return '<label class="btn btn-default' . ($checked ? ' active' : '') . '"' . $hint . '>' .
                Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn']) . $label . '</label>';
        },
    ]) ?>

    <div id="fff-block-<?= $srcTr ?>" class="collapse">
        <?= $form->field($model, 'tr_id')->widget(Select2::class, [
            'data' => $tr,
            'theme' => Select2::THEME_BOOTSTRAP,
            'options' => ['placeholder' => '- выберите -'],
            'pluginOptions' => ['allowClear' => true],
            'hideSearch' => true,
            'pluginEvents' => [
                'change' => 'function() { $("#edffillfkkobasisform-src").val("1"); $("#promptSrc").text("В качестве источника данных выбран запрос на транспорт."); }',
            ],
        ]) ?>

    </div>
    <div id="fff-block-<?= $srcLr ?>" class="collapse">
        <?= $form->field($model, 'lr_id')->widget(Select2::class, [
            'data' => $lr,
            'theme' => Select2::THEME_BOOTSTRAP,
            'options' => ['placeholder' => '- выберите -'],
            'pluginOptions' => ['allowClear' => true],
            'hideSearch' => true,
            'pluginEvents' => [
                'change' => 'function() { $("#edffillfkkobasisform-src").val("2"); $("#promptSrc").text("В качестве источника данных выбран запрос лицензий."); }',
            ],
        ]) ?>

    </div>
    <div id="fff-block-<?= $srcExcel ?>" class="collapse">
        <?= $form->field($model, 'importFile')->fileInput() ?>

    </div>
    <p id="promptSrc" class="text-center text-muted">Выберите тип и источник данных.</p>

    <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Выполнить импорт', ['class' => 'btn btn-success btn-block collapse', 'id' => 'btnSubmitFillFkkoForm']) ?>

    <?php ActiveForm::end(); ?>

</div>
