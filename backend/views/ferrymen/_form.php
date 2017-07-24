<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\Ferrymen;
use common\models\FerrymenTypes;
use common\models\PaymentConditions;

/* @var $this yii\web\View */
/* @var $model common\models\Ferrymen */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dpDrivers common\models\Drivers[] */
/* @var $dpTransport common\models\Transport[] */
?>

<div class="ferrymen-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Введите наименование']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'state_id')->widget(Select2::className(), [
                'data' => Ferrymen::arrayMapOfStatesForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'ft_id')->widget(Select2::className(), [
                'data' => FerrymenTypes::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'pc_id')->widget(Select2::className(), [
                'data' => PaymentConditions::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'phone')->textInput(['maxlength' => true, 'placeholder' => 'Введите телефоны']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'Введите E-mail']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'contact_person')->textInput(['maxlength' => true, 'placeholder' => 'Введите имя', 'title' => 'Введите имя контактного лица']) ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Перевозчики', ['/ferrymen'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
            <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>

        <?php else: ?>
            <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
