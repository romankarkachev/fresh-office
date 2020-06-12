<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\DirectMSSQLQueries;

/* @var $this yii\web\View */
/* @var $model common\models\ReportCompaniesCurator */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="emptycustomers-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/reports/companies-curator'],
        'method' => 'get',
        'options' => ['id' => 'frmSearch'],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'searchResponsible')->widget(Select2::class, [
                        'data' => DirectMSSQLQueries::arrayMapOfManagersForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchPerPage')->textInput(['placeholder' => '∞']) ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('<i class="fa fa-filter"></i> Выполнить отбор', ['class' => 'btn btn-info']) ?>

                <?= Html::a('Отключить отбор', ['/reports/companies-curator'], ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
