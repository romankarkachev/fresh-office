<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use common\models\PaymentOrdersSearch;
use common\models\Ferrymen;

/* @var $this yii\web\View */
/* @var $model common\models\PaymentOrdersSearch */
/* @var $form yii\bootstrap\ActiveForm */

if (Yii::$app->user->can('accountant'))
    $groupStates = PaymentOrdersSearch::fetchGroupStatesForAccountant();
else
    $groupStates = PaymentOrdersSearch::fetchRegularGroupStates();
?>

<div class="payment-orders-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/payment-orders'],
        'method' => 'get',
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'searchPaymentDateStart')->widget(DateControl::className(), [
                                'value' => $model->searchPaymentDateStart,
                                'type' => DateControl::FORMAT_DATE,
                                'language' => 'ru',
                                'displayFormat' => 'php:d.m.Y',
                                'saveFormat' => 'php:Y-m-d',
                                'widgetOptions' => [
                                    'options' => [
                                        'placeholder' => 'дата оплаты с',
                                        'title' => 'Начало периода для отбора по дате оплаты',
                                        'autocomplete' => 'off',
                                    ],
                                    'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                                    'layout' => '<div class="input-group">{input}{picker}</div>',
                                    'pluginOptions' => [
                                        'todayHighlight' => true,
                                        'weekStart' => 1,
                                        'autoclose' => true,
                                    ],
                                    'pluginEvents' => [
                                        'changeDate' => 'function(e) { anyDateOnChange(); }',
                                    ],
                                ],
                            ]) ?>

                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'searchPaymentDateEnd')->widget(DateControl::className(), [
                                'value' => $model->searchPaymentDateEnd,
                                'type' => DateControl::FORMAT_DATE,
                                'language' => 'ru',
                                'displayFormat' => 'php:d.m.Y',
                                'saveFormat' => 'php:Y-m-d',
                                'widgetOptions' => [
                                    'options' => [
                                        'placeholder' => 'дата оплаты по',
                                        'title' => 'Конец периода для отбора по дате оплаты',
                                        'autocomplete' => 'off',
                                    ],
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
                </div>
                <div class="col-md-3 col-lg-2">
                    <?= $form->field($model, 'ferryman_id')->widget(Select2::className(), [
                        'data' => Ferrymen::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'created_by')->widget(Select2::className(), [
                        'data' => \common\models\User::arrayMapForSelect2(\common\models\User::ARRAY_MAP_OF_USERS_BY_LOGIST_ROLE),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                    ]) ?>

                </div>
                <div class="col-md-5">
                    <?= $form->field($model, 'searchGroupStates', [
                        'inline' => true,
                    ])->radioList(ArrayHelper::map($groupStates, 'id', 'name'), [
                        'class' => 'btn-group',
                        'data-toggle' => 'buttons',
                        'unselect' => null,
                        'item' => function ($index, $label, $name, $checked, $value) use ($groupStates) {
                            $hint = '';
                            $key = array_search($value, array_column($groupStates, 'id'));
                            if ($key !== false && isset($groupStates[$key]['hint'])) $hint = ' title="' . $groupStates[$key]['hint'] . '"';

                            return '<label class="btn btn-default' . ($checked ? ' active' : '') . '"' . $hint . '>' .
                                Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn']) . $label . '</label>';
                        },
                    ]) ?>

                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'searchCcp')->widget(Select2::class, [
                        'data' => $model::arrayMapOfCcpForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchOrp')->widget(Select2::class, [
                        'data' => $model::arrayMapOfOrpForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                    ]) ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', ['/payment-orders'], ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
