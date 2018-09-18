<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use common\models\foProjectsSearch;

/* @var $this yii\web\View */
/* @var $model common\models\foProjectsSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */
?>

<div class="projects-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/projects/states-matrix'],
        'method' => 'get',
        'options' => ['id' => 'frm-search', 'class' => ($searchApplied ? 'collapse in' : 'collapse')],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-1">
                    <?= $form->field($model, 'searchPerPage')->textInput() ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchCreatedFrom')->widget(DateControl::className(), [
                        'value' => $model->searchCreatedFrom,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'начало'],
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
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchCreatedTo')->widget(DateControl::className(), [
                        'value' => $model->searchCreatedTo,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'конец'],
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
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'ca_id')->widget(Select2::className(), [
                        'initValueText' => $model->customerName,
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'language' => 'ru',
                        'options' => ['placeholder' => 'Введите наименование'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 1,
                            'language' => 'ru',
                            'ajax' => [
                                'url' => Url::to(['projects/direct-sql-counteragents-list']),
                                'delay' => 500,
                                'dataType' => 'json',
                                'data' => new JsExpression('function(params) { return {q:params.term}; }')
                            ],
                            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                            'templateResult' => new JsExpression('function(result) { return result.text; }'),
                            'templateSelection' => new JsExpression('function (result) { return result.text; }'),
                        ],
                    ]) ?>

                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'searchGroupProjectTypes', [
                        'inline' => true,
                    ])->radioList(foProjectsSearch::fetchGroupProjectTypesIdsForMatrix(), [
                        'class' => 'btn-group',
                        'data-toggle' => 'buttons',
                        'unselect' => null,
                        'item' => function ($index, $label, $name, $checked, $value) {
                            return '<label class="btn btn-default' . ($checked ? ' active' : '') . '">' .
                                Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn']) . $label . '</label>';
                        },
                    ]) ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', ['/projects/states-matrix'], ['class' => 'btn btn-default']) ?>

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
?>
