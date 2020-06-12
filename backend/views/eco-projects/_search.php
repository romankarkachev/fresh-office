<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use backend\controllers\EcoProjectsController;
use kartik\select2\Select2;
use common\models\EcoTypes;

/* @var $this yii\web\View */
/* @var $model common\models\EcoProjectsSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */
/* @var $searchProgresses array */
?>

<div class="eco-projects-search">
    <?php $form = ActiveForm::begin([
        'action' => EcoProjectsController::ROOT_URL_AS_ARRAY,
        'method' => 'get',
        //'options' => ['id' => 'frm-search', 'class' => ($searchApplied ? 'collapse in' : 'collapse')],
        'options' => ['id' => 'frm-search'],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'org_id')->widget(Select2::className(), [
                        'data' => \common\models\Organizations::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'ca_id')->widget(Select2::className(), [
                        'initValueText' => \common\models\TransportRequests::getCustomerName($model->ca_id),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'language' => 'ru',
                        'options' => ['placeholder' => 'Введите наименование'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 1,
                            'language' => 'ru',
                            'ajax' => [
                                'url' => \yii\helpers\Url::to(['projects/direct-sql-counteragents-list']),
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
                <?php if (Yii::$app->user->can('root') || Yii::$app->user->can('ecologist_head')): ?>
                <div class="col-md-2">
                    <?= $form->field($model, 'responsible_id')->widget(Select2::className(), [
                        'data' => \common\models\EcoProjectsAccess::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <?php endif; ?>
                <div class="col-md-2">
                    <?= $form->field($model, 'type_id')->widget(Select2::className(), [
                        'data' => EcoTypes::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'searchProgress', [
                        'inline' => true,
                    ])->radioList(ArrayHelper::map($searchProgresses, 'id', 'name'), [
                        'class' => 'btn-group',
                        'data-toggle' => 'buttons',
                        'unselect' => null,
                        'item' => function ($index, $label, $name, $checked, $value) use ($searchProgresses) {
                            $hint = '';
                            $key = array_search($value, array_column($searchProgresses, 'id'));
                            if ($key !== false && isset($searchProgresses[$key]['hint'])) $hint = ' title="' . $searchProgresses[$key]['hint'] . '"';

                            return '<label class="btn btn-default' . ($checked ? ' active' : '') . '"' . $hint . '>' .
                                Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn']) . $label . '</label>';
                        },
                    ]) ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', EcoProjectsController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
