<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ProductsSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */
?>

<div class="products-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/products'],
        'method' => 'get',
        'options' => ['id' => 'frm-search', 'class' => ($searchApplied ? 'collapse in' : 'collapse')],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="form-group">
                <?= $form->field($model, 'searchField')->textInput(['placeholder' => 'Введите часть наименования, код ФККО, код Fresh Office или ID']) ?>

            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-primary']) ?>

                <?= Html::a('Сброс', ['/products'], ['class' => 'btn btn-default']) ?>

            </div>
        </div>
        <?php ActiveForm::end(); ?>

    </div>
</div>
