<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\EcoProjectsAccess;

/* @var $this yii\web\View */
/* @var $model common\models\EcoProjectsAccess */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="eco-access-form">
    <div class="panel panel-success">
        <div class="panel-heading">Предоставление доступа пользователям</div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin([
                'id' => 'frmNewUserAccess',
                'action' => \backend\controllers\EcoProjectsController::URL_ADD_USER_ACCESS_AS_ARRAY,
                'options' => ['data-pjax' => true],
            ]); ?>

            <?= $form->field($model, 'user_id')->widget(Select2::className(), [
                'data' => EcoProjectsAccess::arrayMapForSelect2($model->project_id),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

            <?= $form->field($model, 'project_id')->hiddenInput()->label(false) ?>

            <div class="form-group">
                <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Добавить', ['class' => 'btn btn-success']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
