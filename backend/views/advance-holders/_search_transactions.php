<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\datecontrol\DateControl;
use yii\bootstrap\ActiveForm;
use backend\controllers\AdvanceHoldersController;

/* @var $this yii\web\View */
/* @var $model common\models\FinanceTransactionsSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $renderRestricted bool признак, определяющий расширенное или ограниченное использование инструмента */

if ($renderRestricted) {
    $action = Url::to(AdvanceHoldersController::ROOT_URL_AS_ARRAY);
}
else {
    $action = Url::to(ArrayHelper::merge(AdvanceHoldersController::URL_TRANSACTIONS_AS_ARRAY, ['id' => $model->user_id]));
}
?>

<div class="appeals-search">
    <?php $form = ActiveForm::begin([
        'action' => $action,
        'method' => 'get',
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'searchPeriodStart')->widget(DateControl::className(), [
                        'value' => $model->searchPeriodStart,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => '- выберите дату -', 'autocomplete' => 'off'],
                            'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                            'layout' => '{input}{picker}{remove}',
                            'pluginOptions' => [
                                'todayHighlight' => true,
                                'weekStart' => 1,
                                'autoclose' => true,
                            ],
                            'pluginEvents' => [
                                'changeDate' => 'function(e) {anyDateOnChange();}',
                            ],
                        ],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchPeriodEnd')->widget(DateControl::className(), [
                        'value' => $model->searchPeriodEnd,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => '- выберите дату -', 'autocomplete' => 'off'],
                            'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                            'layout' => '{input}{picker}{remove}',
                            'pluginOptions' => [
                                'todayHighlight' => true,
                                'weekStart' => 1,
                                'autoclose' => true,
                            ],
                            'pluginEvents' => [
                                'changeDate' => 'function(e) {anyDateOnChange();}',
                            ],
                        ],
                    ]) ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info']) ?>

                <?= Html::a('Отключить отбор', $action, ['class' => 'btn btn-default']) ?>

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
    text = \$button.text();
    \$button.text("Подождите...");
    setTimeout(function () {
        \$button.removeAttr("disabled");
        \$button.text(text);
    }, 1500);
}
JS
, \yii\web\View::POS_BEGIN);
