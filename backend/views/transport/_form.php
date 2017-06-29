<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\Ferrymen;
use common\models\TransportTypes;
use common\models\TransportBrands;

/* @var $this yii\web\View */
/* @var $model common\models\Transport */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="transport-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <?php if ($model->ferryman != null): ?>
        <?= $form->field($model, 'ferryman_id')->hiddenInput()->label(false) ?>

        <?php else: ?>
        <div class="col-md-2 col-lg-2">
            <?= $form->field($model, 'ferryman_id')->widget(Select2::className(), [
                'data' => Ferrymen::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <?php endif ?>
        <div class="col-md-2">
            <?= $form->field($model, 'tt_id')->widget(Select2::className(), [
                'data' => TransportTypes::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'brand_id')->widget(Select2::className(), [
                'data' => TransportBrands::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'vin')->textInput(['maxlength' => true, 'placeholder' => 'Введите VIN', 'title' => 'VIN-код, номер кузова или номер шасси']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'rn')->textInput(['maxlength' => true, 'placeholder' => 'Введите госномер']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'trailer_rn')->textInput(['maxlength' => true, 'placeholder' => 'Госномер прицепа']) ?>

        </div>
    </div>
    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите примечание']) ?>

    <div class="form-group">
        <?php if ($model->ferryman != null): ?>
        <div class="btn-group">
            <button class="btn btn-default btn-lg dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-arrow-left" aria-hidden="true"></i> Вернуться <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><?= Html::a($model->ferryman->name, ['/ferrymen/update', 'id' => $model->ferryman->id], ['title' => 'Вернуться в карточку перевозчика. Изменения не будут сохранены']) ?></li>
                <li><?= Html::a('Транспорт перевозчика', ['/ferrymen-transport', 'TransportSearch' => ['ferryman_id' => $model->ferryman->id]], ['title' => 'Перейти в список автомобилей перевозчика. Изменения не будут сохранены']) ?></li>
            </ul>
        </div>
        <?php else: ?>
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Транспорт', ['/ferrymen-transport'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список автомобилей. Изменения не будут сохранены']) ?>

        <?php endif; ?>
        <?php if ($model->isNewRecord): ?>
            <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>

        <?php else: ?>
            <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
