<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use common\models\TransportRequests;
use common\models\TransportRequestsStates;
use common\models\PeriodicityKinds;
use common\models\TransportTypes;

/* @var $this yii\web\View */
/* @var $model common\models\TransportRequestsSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */

// отбор по идентификатору ФККО
//$form->field($model, 'searchFkko')->widget(Select2::className(), [
//    //'initValueText' => TransportRequests::getCustomerName($model->customer_id),
//    'theme' => Select2::THEME_BOOTSTRAP,
//    'language' => 'ru',
//    'options' => ['placeholder' => 'Код ФККО или наименование'],
//    'pluginOptions' => [
//        'allowClear' => true,
//        'minimumInputLength' => 1,
//        'language' => 'ru',
//        'ajax' => [
//            'url' => Url::to(['fkko/list-for-select2']),
//            'delay' => 500,
//            'dataType' => 'json',
//            'data' => new JsExpression('function(params) { return {q:params.term}; }')
//        ],
//        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
//        'templateResult' => new JsExpression('function(result) { return result.text; }'),
//        'templateSelection' => new JsExpression('function (result) { return result.text; }'),
//    ],
//])

$options = [
    'action' => ['/transport-requests'],
    'method' => 'get',
];
if (Yii::$app->user->identity->username == 'administrator')
    $options['options'] = ['id' => 'frm-search', 'class' => ($searchApplied ? 'collapse in' : 'collapse')];
?>

<div class="transport-requests-search">
    <?php $form = ActiveForm::begin($options); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'state_id')->widget(Select2::className(), [
                        'data' => TransportRequestsStates::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'periodicity_id')->widget(Select2::className(), [
                        'data' => PeriodicityKinds::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'region_id')->widget(Select2::className(), [
                        'data' => TransportRequests::arrayMapOfRegionsForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchFkkoName')->textInput(['placeholder' => 'Введите наименование отхода']) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchTransportType')->widget(Select2::className(), [
                        'data' => TransportTypes::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <label for="<?= strtolower($model->formName() . '-searchOnlyFavorite') ?>" class="control-label"><?= $model->attributeLabels()['searchOnlyFavorite'] ?></label>
                    <?= $form->field($model, 'searchOnlyFavorite')->checkbox()->label(false) ?>

                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'customer_id')->widget(Select2::className(), [
                        'initValueText' => TransportRequests::getCustomerName($model->customer_id),
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
                <div class="col-md-2">
                    <?= $form->field($model, 'searchDateStart')->widget(DateControl::className(), [
                        'value' => $model->searchDateStart,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'Начало периода'],
                            'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                            'layout' => '<div class="input-group">{input}{picker}</div>',
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
                    <?= $form->field($model, 'searchDateEnd')->widget(DateControl::className(), [
                        'value' => $model->searchDateEnd,
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'ru',
                        'displayFormat' => 'php:d.m.Y',
                        'saveFormat' => 'php:Y-m-d',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'Конец периода'],
                            'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                            'layout' => '<div class="input-group">{input}{picker}</div>',
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
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', ['/transport-requests'], ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
