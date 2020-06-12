<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use common\models\Ferrymen;
use backend\controllers\FerrymenPricesController;

/* @var $this yii\web\View */
/* @var $model common\models\FerrymenPrices */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="ferrymen-prices-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'ferryman_id')->widget(Select2::class, [
                'data' => Ferrymen::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'pluginOptions' => ['allowClear' => true],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'price', [
                'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
            ])->widget(MaskedInput::class, [
                'clientOptions' => [
                    'alias' =>  'numeric',
                    'groupSeparator' => ' ',
                    'autoUnmask' => true,
                    'autoGroup' => true,
                    'removeMaskOnSubmit' => true,
                ],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => '0',
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'cost', [
                'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
            ])->widget(MaskedInput::class, [
                'clientOptions' => [
                    'alias' =>  'numeric',
                    'groupSeparator' => ' ',
                    'autoUnmask' => true,
                    'autoGroup' => true,
                    'removeMaskOnSubmit' => true,
                ],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => '0',
            ]) ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . FerrymenPricesController::ROOT_LABEL, FerrymenPricesController::URL_ROOT_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
