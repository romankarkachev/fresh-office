<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use common\models\TransportRequests;

/* @var $this yii\web\View */
/* @var $model common\models\CorrespondencePackagesSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */

$groups = \common\models\CorrespondencePackagesSearch::fetchGroupProjectStatesIds();
?>

<div class="correspondence-packages-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/correspondence-packages'],
        'method' => 'get',
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-body">
            <div class="row">
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
                <div class="col-md-6">
                    <?= $form->field($model, 'searchGroupProjectStates', [
                        'inline' => true,
                    ])->radioList(\yii\helpers\ArrayHelper::map(\common\models\CorrespondencePackagesSearch::fetchGroupProjectStatesIds(), 'id', 'name'), [
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
