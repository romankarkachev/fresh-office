<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use backend\controllers\PbxEmployeesController;
use common\models\pbxDepartments;

/* @var $this yii\web\View */
/* @var $model common\models\pbxEmployees */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="pbx-employee-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'department_id')->widget(Select2::className(), [
                'data' => pbxDepartments::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите отдел -'],
            ]) ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'name')->textInput(['placeholder' => 'Введите ФИО сотрудника']) ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . PbxEmployeesController::ROOT_LABEL, PbxEmployeesController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
