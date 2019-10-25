<?php

/* @var $this yii\web\View */
/* @var $model common\models\Ferrymen */
?>
</div>
<div role="tabpanel" class="tab-pane" id="contacts">
    <div class="row">
        <div class="col-md-12"><p class="lead">Диспетчер</p></div>
        <div class="col-md-2">
            <?= $form->field($model, 'contact_person')->textInput(['maxlength' => true, 'placeholder' => 'Введите имя', 'title' => 'Введите имя контактного лица']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'phone', ['template' => '{label}<div class="input-group"><span class="input-group-addon">+7</span>{input}</div>{error}'])->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '(999) 999-99-99',
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите номер телефона']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'Введите E-mail']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'post')->textInput(['maxlength' => true, 'placeholder' => 'Введите должность']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12"><p class="lead">Руководитель</p></div>
        <div class="col-md-2">
            <?= $form->field($model, 'contact_person_dir')->textInput(['maxlength' => true, 'placeholder' => 'Введите имя', 'title' => 'Введите имя контактного лица']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'phone_dir', ['template' => '{label}<div class="input-group"><span class="input-group-addon">+7</span>{input}</div>{error}'])->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '(999) 999-99-99',
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите номер телефона']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'email_dir')->textInput(['maxlength' => true, 'placeholder' => 'Введите E-mail']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'post_dir')->textInput(['maxlength' => true, 'placeholder' => 'Введите должность']) ?>

        </div>
    </div>
</div>
