<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\EdfFillFkkoBasisForm */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $tr array массив запросов на транспорт */
/* @var $lr array массив запросов лицензий */
?>

<div class="transport-requests-dialogs-form">
    <?php $form = ActiveForm::begin([
        'action' => \backend\controllers\EdfController::FILL_FKKO_TR_BASIS_AS_ARRAY,
        'options' => ['id' => 'frmFillFkko'],
    ]); ?>

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

    <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Добавить', ['class' => 'btn btn-success', 'id' => 'btnSubmitFillFkkoForm']) ?>

    <?= $form->field($model, 'src', ['template' => "{input}"])->hiddenInput()->label(false) ?>

    <p id="promptSrc" class="text-muted">Выберите источник данных.</p>
    <?php ActiveForm::end(); ?>

</div>
