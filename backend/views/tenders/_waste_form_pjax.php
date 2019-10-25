<?php

use backend\controllers\TendersController;
use kartik\typeahead\Typeahead;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model common\models\TendersTp */
/* @var $form yii\bootstrap\ActiveForm */

$formNameId = strtolower($model->formName());
?>

<div class="tender-waste-form">
    <?php $form = ActiveForm::begin([
        'id' => \common\models\TendersTp::DOM_IDS['PJAX_FORM_ID'],
        'action' => TendersController::URL_CREATE_WASTE_AS_ARRAY,
        'options' => ['data-pjax' => true],
    ]); ?>

    <div class="panel panel-success">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'fkko_name')->widget(Typeahead::class, [
                        'options' => [
                            'value' => $model->fkko_name,
                            'placeholder' => 'Код ФККО или наименование',
                            'autocomplete' => 'off',
                        ],
                        'scrollable' => true,
                        'pluginOptions' => ['highlight' => true],
                        'dataset' => [
                            [
                                'remote' => [
                                    'url' => Url::to(['transport-requests/list-of-fkko-for-typeahead']),
                                    'rateLimitWait' => 500,
                                    'prepare' => new JsExpression('
function prepare(query, settings) {
    settings.url += "?q=" + query + "";
    return settings;
}
                                    ')
                                ],
                                'limit' => 10,
                                'display' => 'value',
                            ],
                        ],
                        'pluginEvents' => [
                            'typeahead:select' => '
function(ev, suggestion) {
    $("#' . $formNameId . '-fkko_id").val(suggestion.id); // идентификатор ФККО
}
                            ',
                            'typeahead:asyncreceive' => '
function() {
    $("#' . $formNameId . '-fkko_id").val("");
}
                            ',
                        ],
                    ])->label('Добавление видов отходов в табличную часть') ?>

                </div>
                <div class="col-md-1">
                    <label class="control-label btn-block">&nbsp;</label>
                    <?= Html::submitButton('Добавить <i class="fa fa-arrow-down"></i> ', ['class' => 'btn btn-success']) ?>

                </div>
            </div>
            <p class="text-muted text-justify">Обратите внимание, что данная форма &mdash; интерактивная, то есть изменения сохранять не нужно, все действия применяются на лету.</p>
        </div>
    </div>
    <?= $form->field($model, 'fkko_id')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'tender_id')->hiddenInput()->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
