<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\TransportTypes;
use common\models\TechnicalConditions;

/* @var $this yii\web\View */
/* @var $model common\models\Transport */
/* @var $form yii\bootstrap\ActiveForm */

$this->title = 'Новый автомобиль перевозчика ' . $model->ferryman->name . HtmlPurifier::process(' &mdash; Перевозчики | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
$this->params['breadcrumbs'][] = ['label' => $model->ferryman->name, 'url' => ['/ferrymen/update', 'id' => $model->ferryman->id]];
$this->params['breadcrumbs'][] = 'Новый автомобиль *';
?>

<div class="transport-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ferryman_id')->hiddenInput()->label(false) ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'tt_id')->widget(Select2::className(), [
                'data' => TransportTypes::arrayMapForSelect2(),
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
        <div class="col-md-2">
            <?= $form->field($model, 'tc_id')->widget(Select2::className(), [
                'data' => TechnicalConditions::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите примечание']) ?>

    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . $model->ferryman->name, ['/ferrymen/update', 'id' => $model->ferryman->id], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в карточку перевозчика. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
            <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>

        <?php else: ?>
            <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
