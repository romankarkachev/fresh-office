<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\CEAttachedFilesSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */
?>

<div class="attached-files-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/attached-files'],
        'method' => 'get',
    ]); ?>

    <div class="card">
        <div class="card-header card-header-info card-header-inverse"><i class="fa fa-filter"></i> Форма отбора</div>
        <div class="card-block">
            <?= $form->field($model, 'ofn')->textInput(['placeholder' => 'Введите имя файла']) ?>

            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info']) ?>

                <?= Html::a('Отключить отбор', ['/attached-files'], ['class' => 'btn btn-secondary']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
