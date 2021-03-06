<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\UserSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */
?>

<div class="users-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/users'],
        'method' => 'get',
        'options' => ['id' => 'frm-search', 'class' => ($searchApplied ? 'collapse in' : 'collapse')],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="form-group">
                <?= $form->field($model, 'searchEntire')->textInput(['placeholder' => 'Введите значение для поиска']) ?>

            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-primary']) ?>

                <?= Html::a('Сброс', ['/users'], ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    <?php ActiveForm::end(); ?>

    </div>
</div>
