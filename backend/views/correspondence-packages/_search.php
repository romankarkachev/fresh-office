<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use common\models\CorrespondencePackagesStates;
use common\models\CorrespondencePackagesSearch;
use common\models\TransportRequests;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\CorrespondencePackagesSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */

$groups = CorrespondencePackagesSearch::fetchGroupProjectStatesIds();
if (Yii::$app->user->can('sales_department_manager')) $groups = CorrespondencePackagesSearch::fetchGroupProjectStatesIdsForManager();

$packagesTypes = CorrespondencePackagesSearch::fetchFilterPackagesTypes();
?>

<div class="correspondence-packages-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/correspondence-packages'],
        'method' => 'get',
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'track_num')->textInput(['placeholder' => 'Введите номер'])?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'fo_id_company')->widget(Select2::className(), [
                        'initValueText' => TransportRequests::getCustomerName($model->fo_id_company),
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
                <?php if (Yii::$app->user->can('root') || Yii::$app->user->can('operator_head') || Yii::$app->user->can('sales_department_manager')): ?>
                <div class="col-md-3">
                    <?= $form->field($model, 'manager_id')->widget(Select2::className(), [
                        'data' => User::arrayMapForSelect2(User::ARRAY_MAP_OF_USERS_BY_MANAGER_AND_ECOLOGIST_ROLE),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchPackageType', [
                        'inline' => true,
                    ])->radioList(ArrayHelper::map(CorrespondencePackagesSearch::fetchFilterPackagesTypes(), 'id', 'name'), [
                        'class' => 'btn-group',
                        'data-toggle' => 'buttons',
                        'unselect' => null,
                        'item' => function ($index, $label, $name, $checked, $value) use ($packagesTypes) {
                            $hint = '';
                            $key = array_search($value, array_column($packagesTypes, 'id'));
                            if ($key !== false && isset($groups[$key]['hint'])) $hint = ' title="' . $packagesTypes[$key]['hint'] . '"';

                            return '<label class="btn btn-default' . ($checked ? ' active' : '') . '"' . $hint . '>' .
                                Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn']) . $label . '</label>';
                        },
                    ]) ?>

                </div>
                <?php endif; ?>
                <div class="col-md-2">
                    <?= $form->field($model, 'cps_id')->widget(Select2::className(), [
                        'data' => CorrespondencePackagesStates::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <?php if (Yii::$app->user->can('root') || Yii::$app->user->can('operator_head')): ?>
            </div>
            <div class="row">
                <?php endif; ?>
                <div class="col-md-6">
                    <?= $form->field($model, 'searchGroupProjectStates', [
                        'inline' => true,
                    ])->radioList(ArrayHelper::map($groups, 'id', 'name'), [
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
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info']) ?>

                <?= Html::a('Отключить отбор', ['/correspondence-packages'], ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
