<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\ProjectsRatingsSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */

$searchDetailedId = strtolower($model->formName() . '-searchDetailed');
?>

<div class="projects-ratings-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/projects/ratings'],
        'method' => 'get',
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-1">
                    <?= $form->field($model, 'searchPerPage')->textInput(['placeholder' => 100]) ?>

                </div>
                <div class="col-md-1">
                    <?= $form->field($model, 'searchProjectIds')->textInput(['placeholder' => 'Введите идентификатор проекта', 'title' => 'Вы можете ввести один или несколько идентификаторов проектов через запятую без пробелов']) ?>

                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'ca_id')->widget(Select2::className(), [
                        'initValueText' => $model->caName,
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'language' => 'ru',
                        'options' => ['placeholder' => 'Введите наименование'],
                        'pluginOptions' => [
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
                            'allowClear' => true,
                        ],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <label for="<?= $searchDetailedId ?>" class="control-label">Детализировать</label>
                    <div class="form-group">
                        <div class="checkbox" style="margin-top:5px;">
                            <?= Html::input('checkbox', 'ProjectsRatingsSearch[searchDetailed]', 1, ['id' => $searchDetailedId, 'checked' => !empty($model->searchDetailed)]) ?>

                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info']) ?>

                <?= Html::a('Отключить отбор', ['/projects/ratings'], ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
