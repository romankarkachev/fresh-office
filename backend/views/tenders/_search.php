<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\controllers\TendersController;

/* @var $this yii\web\View */
/* @var $model common\models\TendersSearch */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="tenders-search">
    <?php $form = ActiveForm::begin([
        'action' => TendersController::ROOT_URL_AS_ARRAY,
        'method' => 'get',
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <?= $form->field($model, 'title')->textInput(['placeholder' => 'Введите часть наименования']) ?>

            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', TendersController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
