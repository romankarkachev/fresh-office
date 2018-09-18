<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use backend\controllers\PbxInternalPhoneNumbersController;
use common\models\pbxDepartments;
use common\models\pbxEmployees;

/* @var $this yii\web\View */
/* @var $model common\models\pbxInternalPhoneNumber */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="pbx-internal-phone-number-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'department_id')->widget(Select2::className(), [
                'data' => pbxDepartments::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите отдел -'],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'employee_id')->widget(Select2::className(), [
                'data' => pbxEmployees::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите сотрудника -'],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'phone_number')->textInput(['placeholder' => 'Введите номер', 'title' => 'Введите внутренний номер абонента']) ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . PbxInternalPhoneNumbersController::ROOT_LABEL, PbxInternalPhoneNumbersController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
