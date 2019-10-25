<?php

use kartik\rating\StarRating;
use common\components\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model customer\models\ProjectRatingForm */
/* @var $form common\components\bootstrap\ActiveForm */
?>

<div class="request-form">
    <?php $form = ActiveForm::begin([
        'id' => 'frmRateProject',
        'action' => ['default/project-rating'],
    ]); ?>

    <?= $form->field($model, 'rate')->widget(StarRating::class, [
        'options' => [
            'data-id' => $model->ca_id,
            'data-ca_id' => $model->project_id,
        ],
        'pluginOptions' => [
            'min' => 0,
            'max' => 5,
            'step' => 1,
            'size' => 'sm',
            'theme' => 'krajee-fa',
            'showClear' => false,
            'showCaption' => false,
        ],
    ])->label(false) ?>

    <?= $form->field($model, 'comment')->textarea(['autofocus' => true, 'rows' => 6, 'placeholder' => 'Введите текст своего комментария'])->label('Пожалуйста, расскажите нам, как стать лучше') ?>

    <?= $form->field($model, 'ca_id')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'project_id')->hiddenInput()->label(false) ?>

    <?= Html::submitButton('Отправить', ['class' => 'btn btn-success btn-lg']) ?>

    <?php ActiveForm::end(); ?>

</div>
