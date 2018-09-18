<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;

/* @var $this yii\web\View */
/* @var $model common\models\ReportFileStorageStats */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */
/* @var $queryString string */
?>

<div class="fsstats-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/reports/pbx-calls-has-tasks-assigned'],
        'method' => 'get',
        'options' => ['id' => 'frm-search'],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'searchPeriodEnd')->widget(DateControl::className(), [
                        'value' => $model->searchPeriodEnd,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => '- выберите день -'],
                            'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                            'layout' => '{input}{picker}{remove}',
                            'pluginOptions' => [
                                'todayHighlight' => true,
                                'weekStart' => 1,
                                'autoclose' => true,
                            ],
                            'pluginEvents' => [
                                'changeDate' => 'function(e) {
anyDateOnChange();
                                }',
                            ],
                        ],
                    ])->label('На дату') ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('<i class="fa fa-repeat"></i> Сформировать', ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'id' => 'btnSearch']) ?>

                <?= Html::a('<i class="fa fa-file-excel-o" aria-hidden="true"></i> Экспорт в Excel', '/reports/pbx-calls-has-tasks-assigned?export=true' . $queryString, ['class' => 'btn btn-default pull-right']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
// Функция-обработчик изменения даты в любом из соответствующих полей.
//
function anyDateOnChange() {
    \$button = $("#btnSearch");
    \$button.attr("disabled", "disabled");
    text = \$button.html();
    \$button.text("Подождите...");
    setTimeout(function () {
        \$button.removeAttr("disabled");
        \$button.html(text);
    }, 1500);
}
JS
, \yii\web\View::POS_BEGIN);
