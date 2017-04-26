<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\DirectMSSQLQueries;

/* @var $this yii\web\View */
/* @var $model common\models\ReportCaDuplicates */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */
?>

<div class="caduplicates-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/reports/ca-duplicates'],
        'method' => 'get',
        'options' => ['id' => 'frm-search', 'class' => ($searchApplied ? 'collapse in' : 'collapse')],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'searchResponsibleId')->widget(Select2::className(), [
                        'data' => DirectMSSQLQueries::arrayMapOfManagersForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                    ]) ?>

                </div>
                <div class="col-md-1">
                    <?= $form->field($model, 'searchPerPage')->textInput() ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info']) ?>

                <?= Html::a('Отключить отбор', ['/reports/ca-duplicates'], ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
