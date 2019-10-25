<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use yii\web\JsExpression;
use backend\controllers\EdfController;
use common\models\DocumentsTypes;
use common\models\ContractTypes;
use common\models\Edf;
use common\models\EdfStates;
use common\models\User;
use common\models\TransportRequests;

/* @var $this yii\web\View */
/* @var $model common\models\EdfSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */

$groups = \common\models\EdfSearch::fetchGroupStatesIds();
?>

<div class="edf-search">
    <?php $form = ActiveForm::begin([
        'action' => EdfController::ROOT_URL_AS_ARRAY,
        'method' => 'get',
        //'options' => ['id' => 'frm-search', 'class' => ($searchApplied ? 'collapse in' : 'collapse')],
        'options' => ['id' => 'frm-search', 'class' => 'collapse in'],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'doc_num')->textInput(['maxlength' => true, 'placeholder' => '№ документа']) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'type_id')->widget(Select2::className(), [
                        'data' => DocumentsTypes::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'ct_id')->widget(Select2::className(), [
                        'data' => ContractTypes::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchTypical')->widget(Select2::className(), [
                        'data' => Edf::arrayMapOfFilterTypicalForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchScan')->widget(Select2::className(), [
                        'data' => Edf::arrayMapOfFilterScanForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchOriginal')->widget(Select2::className(), [
                        'data' => Edf::arrayMapOfFilterOriginalForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'state_id')->widget(Select2::className(), [
                        'data' => EdfStates::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'manager_id')->widget(Select2::className(), [
                        'data' => User::arrayMapForSelect2(User::ARRAY_MAP_OF_USERS_BY_MANAGER_AND_ECOLOGIST_ROLE),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'fo_ca_id')->widget(Select2::className(), [
                        'initValueText' => TransportRequests::getCustomerName($model->fo_ca_id),
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
                <div class="col-md-3">
                    <?= $form->field($model, 'searchGroupStates', [
                        'inline' => true,
                    ])->radioList(\yii\helpers\ArrayHelper::map($groups, 'id', 'name'), [
                        'class' => 'btn-group',
                        'data-toggle' => 'buttons',
                        'unselect' => null,
                        'item' => function ($index, $label, $name, $checked, $value) use ($groups) {
                            $hint = '';
                            $key = array_search($value, array_column($groups, 'id'));
                            if ($key !== false && isset($groups[$key]['hint'])) $hint = ' title="' . $groups[$key]['hint'] . '"';

                            return '<label class="btn btn-default' . ($checked ? ' active' : '') . '"' . $hint . '>' .
                                Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn']) . $label . '</label>';
                        },
                    ]) ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::submitButton('<i class="fa fa-filter"></i> Просроченные', ['name' => 'outdated', 'class' => 'btn btn-info', 'id' => 'btnSearchOutdated', 'title' => 'Выполнить отбор документов с просроченным сроком действия и наложением других выбранных значений']) ?>

                <?= Html::a('Отключить отбор', EdfController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
