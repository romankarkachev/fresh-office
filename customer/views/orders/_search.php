<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use common\components\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;
use common\models\foProjectsSearch;

/* @var $this yii\web\View */
/* @var $model common\models\foProjectsSearch */
/* @var $form common\components\bootstrap\ActiveForm */

$searchForCustomer = foProjectsSearch::fetchGroupSearchForCustomer();
?>

<div class="orders-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/orders'],
        'method' => 'get',
    ]); ?>

    <div class="card">
        <div class="card-header card-header-info card-header-inverse"><i class="fa fa-filter"></i> Форма отбора</div>
        <div class="card-block">
            <div class="row">
                <div class="col-md-auto">
                    <?= $form->field($model, 'searchPerPage')->textInput(['placeholder' => '∞']) ?>

                </div>
                <div class="col-md-auto">
                    <?= $form->field($model, 'searchId')->textInput([
                        'placeholder' => 'Введите номер(а) заказа(ов)',
                        'title' => 'Вы можете ввести один или несколько номеров заказов через запятую без пробелов',
                    ])->label('№ заказов') ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchVivozDateFrom')->widget(DateControl::className(), [
                        'value' => $model->searchVivozDateFrom,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'Начало периода', 'title' => 'Дата вывоза'],
                            'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                            'layout' => '<div class="input-group">{input}{picker}</div>',
                            'pickerButton' => '<span class="input-group-addon kv-date-calendar" title="Выбрать дату"><i class="fa fa-calendar" aria-hidden="true"></i></span>',
                            // можно и добавить, но верстка ломается:
                            //'removeButton' => '<span class="input-group-addon kv-date-remove" title="Очистить поле"><i class="fa fa-remove" aria-hidden="true"></i></span>',
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
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchVivozDateTo')->widget(DateControl::className(), [
                        'value' => $model->searchVivozDateTo,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'Конец периода', 'title' => 'Дата вывоза'],
                            'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                            'layout' => '<div class="input-group">{input}{picker}</div>',
                            'pickerButton' => '<span class="input-group-addon kv-date-calendar" title="Выбрать дату"><i class="fa fa-calendar" aria-hidden="true"></i></span>',
                            // можно и добавить, но верстка ломается:
                            //'removeButton' => '<span class="input-group-addon kv-date-remove" title="Очистить поле"><i class="fa fa-remove" aria-hidden="true"></i></span>',
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
                    ]) ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'searchForCustomerByState', [
                        'inline' => true,
                    ])->radioList(ArrayHelper::map($searchForCustomer, 'id', 'name'), [
                        'class' => 'btn-group',
                        'data-toggle' => 'buttons',
                        'unselect' => null,
                        'item' => function ($index, $label, $name, $checked, $value) use ($searchForCustomer) {
                            $hint = '';
                            $key = array_search($value, array_column($searchForCustomer, 'id'));
                            if ($key !== false && isset($searchForCustomer[$key]['hint'])) $hint = ' title="' . $searchForCustomer[$key]['hint'] . '"';

                            return '<label class="btn btn-secondary' . ($checked ? ' active' : '') . '"' . $hint . '>' .
                                Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn']) . $label . '</label>';
                        },
                    ]) ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('<i class="fa fa-filter"></i> Выполнить отбор', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', ['/orders'], ['class' => 'btn btn-secondary']) ?>

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
    if (!\$button.is(":disabled")) {
        \$button.attr("disabled", "disabled");
        text = \$button.html();
        \$button.text("Подождите...");
        setTimeout(function () {
            \$button.removeAttr("disabled");
            \$button.html(text);
        }, 1500);
    }
}
JS
, \yii\web\View::POS_BEGIN);
?>
