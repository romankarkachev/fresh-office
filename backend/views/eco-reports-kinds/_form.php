<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\EcoReportsKinds;
use backend\controllers\EcoReportsKindsController;

/* @var $this yii\web\View */
/* @var $model common\models\EcoReportsKinds */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="eco-reports-kinds-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Введите наименование', 'autofocus' => true]) ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'gov_agency')->textInput(['maxlength' => true, 'placeholder' => 'Введите контролирующего наименование']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'periodicity')->widget(Select2::class, [
                'data' => EcoReportsKinds::arrayMapOfPeriodicitiesForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Регламентированные отчеты', EcoReportsKindsController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
