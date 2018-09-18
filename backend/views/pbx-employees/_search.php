<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use backend\controllers\PbxEmployeesController;
use common\models\pbxDepartments;

/* @var $this yii\web\View */
/* @var $model common\models\pbxEmployeesSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */
?>

<div class="pbx-employees-search">
    <?php $form = ActiveForm::begin([
        'action' => PbxEmployeesController::ROOT_URL_AS_ARRAY,
        'method' => 'get',
        'options' => ['id' => 'frm-search', 'class' => ($searchApplied ? 'collapse in' : 'collapse')],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'department_id')->widget(Select2::className(), [
                        'data' => pbxDepartments::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите отдел -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'name')->textInput(['placeholder' => 'Введите имя сотрудника для поиска']) ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', PbxEmployeesController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
