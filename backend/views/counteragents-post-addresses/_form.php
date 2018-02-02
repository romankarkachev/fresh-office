<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\CounteragentsPostAddresses */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="counteragents-post-addresses-form">
    <?php $form = ActiveForm::begin([
        'id' => 'frmNewPostAddress',
    ]); ?>

    <?= $form->field($model, 'counteragent_id')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'src_address')->textInput(['placeholder' => 'Введите адрес'])->label('Почтовый адрес') ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'zip_code')->textInput(['maxlength' => true, 'placeholder' => 'Индекс']) ?>

        </div>
        <div class="col-md-10">
            <?= $form->field($model, 'address_m')->textInput(['placeholder' => 'Нормализованный адрес']) ?>

        </div>
    </div>
    <?= $form->field($model, 'comment')->textarea(['rows' => 2, 'placeholder' => 'Введите произвольный комментарий']) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success', 'id' => 'btnSubmit']) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
$("#frmNewPostAddress").on("submit", function (e) {
    e.preventDefault();

    return false;
});

JS
, \yii\web\View::POS_READY);
?>
