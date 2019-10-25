<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\controllers\CompaniesController;

/* @var $this yii\web\View */
/* @var $model common\models\CompaniesSearch */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="companies-search">
    <?php $form = ActiveForm::begin([
        'action' => CompaniesController::ROOT_URL_AS_ARRAY,
        'method' => 'get',
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="form-group">
                <?= $form->field($model, 'searchEntire')->textInput(['placeholder' => 'Поиск по наименованию, ИНН, ОГРН']) ?>

            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', CompaniesController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
