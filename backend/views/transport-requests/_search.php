<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\TransportRequestsSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */
?>

<div class="transport-requests-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/transport-requests'],
        'method' => 'get',
        'options' => ['id' => 'frm-search', 'class' => ($searchApplied ? 'collapse in' : 'collapse')],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info']) ?>

                <?= Html::a('Отключить отбор', ['/transport-requests'], ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php // echo $form->field($model, 'customer_name') ?>

    <?php // echo $form->field($model, 'region_id') ?>

    <?php // echo $form->field($model, 'city_id') ?>

    <?php // echo $form->field($model, 'address') ?>

    <?php // echo $form->field($model, 'state_id') ?>

    <?php // echo $form->field($model, 'comment_manager') ?>

    <?php // echo $form->field($model, 'comment_logist') ?>

    <?php // echo $form->field($model, 'our_loading') ?>

    <?php // echo $form->field($model, 'periodicity_id') ?>

    <?php // echo $form->field($model, 'special_conditions') ?>

    <?php // echo $form->field($model, 'spec_free') ?>

    <?php // echo $form->field($model, 'spec_hose') ?>

    <?php // echo $form->field($model, 'spec_cond') ?>

    <?php ActiveForm::end(); ?>

</div>
