<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\controllers\PbxDepartmentsController;

/* @var $this yii\web\View */
/* @var $model common\models\pbxDepartmentsSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */
?>

<div class="pbx-department-search">
    <?php $form = ActiveForm::begin([
        'action' => PbxDepartmentsController::ROOT_URL_AS_ARRAY,
        'method' => 'get',
        'options' => ['id' => 'frm-search', 'class' => ($searchApplied ? 'collapse in' : 'collapse')],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <?= $form->field($model, 'name')->textInput(['placeholder' => 'Введите наименование для поиска']) ?>

            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', PbxDepartmentsController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
