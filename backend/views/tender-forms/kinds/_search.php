<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\controllers\TenderFormsController;

/* @var $this yii\web\View */
/* @var $model common\models\TenderFormsKindsSearch */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="tenders-forms-kinds-search">
    <?php $form = ActiveForm::begin([
        'action' => TenderFormsController::URL_KINDS_INDEX_AS_ARRAY,
        'method' => 'get',
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <?= $form->field($model, 'name')->textInput(['placeholder' => 'Введите наименование для поиска']) ?>

            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', TenderFormsController::URL_KINDS_INDEX_AS_ARRAY, ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
