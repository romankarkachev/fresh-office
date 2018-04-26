<?php

use kartik\rating\StarRating;
use common\components\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model customer\models\ProjectRatingForm */
/* @var $form common\components\bootstrap\ActiveForm */
?>

<div class="request-form">
    <?php $form = ActiveForm::begin([
        'id' => 'frmRateProject',
        'action' => ['default/rate-project'],
    ]); ?>

    <?= StarRating::widget(['name' => 'rating_35', 'value' => $model->rate, 'pluginOptions' => ['displayOnly' => true, 'theme' => 'krajee-fa',]]) ?>

    <?= $form->field($model, 'comment')->textarea(['autofocus' => true, 'rows' => 6, 'placeholder' => 'Введите текст своего комментария'])->label('Пожалуйста, расскажите нам, как стать лучше') ?>

    <?= $form->field($model, 'ca_id')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'project_id')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'rate')->hiddenInput()->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
