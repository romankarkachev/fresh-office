<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\DirectMSSQLQueries;

/* @var $this yii\web\View */
/* @var $model common\models\ReportNofinances */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */
?>

<div class="nofinances-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/reports/nofinances'],
        'method' => 'get',
        'options' => ['id' => 'frm-search'],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'searchResponsible')->widget(Select2::className(), [
                        'data' => DirectMSSQLQueries::arrayMapOfManagersForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchPerPage')->textInput() ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('<i class="fa fa-filter"></i> Выполнить отбор', ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default')]) ?>

                <?= Html::a('Отключить отбор', ['/reports/nofinances'], ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
