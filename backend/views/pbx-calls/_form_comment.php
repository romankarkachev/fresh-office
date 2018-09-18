<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model common\models\pbxCallsComments */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="pbx-calls-comments-form">
    <?php Pjax::begin(['id' => 'pjax-new-comment', 'enablePushState' => false]); ?>

    <?php $form = ActiveForm::begin([
        'id' => 'frmNewComment',
        'enableAjaxValidation' => true,
        'validationUrl' => ['/pbx-calls/validate-comment'],
        'options' => ['data-pjax' => 1],
    ]); ?>

    <?= $form->field($model, 'contents')->textarea(['autofocus' => true, 'rows' => 3, 'placeholder' => 'Введите текст комментария']) ?>

    <?= $form->field($model, 'call_id')->hiddenInput()->label(false) ?>

    <p class="text-right">
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Добавить комментарий', ['class' => 'btn btn-success']) ?>
    </p>

    <?php ActiveForm::end(); ?>

    <?php Pjax::end(); ?>

</div>
