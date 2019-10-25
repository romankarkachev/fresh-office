<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\controllers\TendersPlatformsController;

/* @var $this yii\web\View */
/* @var $model common\models\TendersPlatforms */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="tenders-platforms-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Введите наименовние', 'autofocus' => true]) ?>

        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'href')->textInput(['maxlength' => true]) ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . TendersPlatformsController::ROOT_LABEL, TendersPlatformsController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?= $model->renderSubmitButtons() ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
