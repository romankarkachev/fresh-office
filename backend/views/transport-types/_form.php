<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model common\models\TransportTypes */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="transport-types-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-8">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Введите наименование']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'unloading_time', [
                'template' => '{label}<div class="input-group">{input}<span class="input-group-addon">мин.</span></div>{error}',
            ])->widget(MaskedInput::class, [
                'clientOptions' => ['alias' =>  'numeric'],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => '0',
                'title' => 'Время в минутах, которое требуется для полной разгрузки транспортного средства',
            ])->label('Время разгрузки') ?>

        </div>
        <div class="col-md-2">
            <label for="<?= strtolower($model->formName()) ?>-is_spec" class="control-label"><?= $model->getAttributeLabel('is_spec') ?></label>
            <?= $form->field($model, 'is_spec')->checkbox()->label(false) ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Типы техники', ['/transport-types'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
            <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>

        <?php else: ?>
            <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
$('input').iCheck({
    checkboxClass: 'icheckbox_square-green',
});
JS
, yii\web\View::POS_READY);
?>
