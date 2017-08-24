<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ReportNofinances */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */
?>

<div class="correspondenceanalytics-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/reports/correspondence-analytics'],
        'method' => 'get',
        'options' => ['id' => 'frm-search'],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('<i class="fa fa-repeat"></i> Сформировать', ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default')]) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
