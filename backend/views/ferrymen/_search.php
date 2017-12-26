<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\typeahead\Typeahead;

/* @var $this yii\web\View */
/* @var $model common\models\FerrymenSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */

$template = '<div><h5><strong>{{value}}</strong></h5>' . '<em class="text-muted">Водители: {{drivers}}</em><br/><em class="text-muted">Транспорт: {{transport}}</em></div>';
?>

<div class="ferrymen-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/ferrymen'],
        'method' => 'get',
        //'options' => ['id' => 'frm-search', 'class' => ($searchApplied ? 'collapse in' : 'collapse')],
        'options' => ['id' => 'frm-search'],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <?= $form->field($model, 'name')->widget(Typeahead::className(), [
                'container' => ['id' => 'frmFerrymanSearchEntire'],
                'options' => ['placeholder' => 'Введите значение поиска'],
                'scrollable' => true,
                'pluginOptions' => ['highlight' => true],
                'dataset' => [
                    [
                        'remote' => [
                            'url' => Url::to(['ferrymen/list-of-ferrymen-for-typeahead']),
                            'rateLimitWait' => 500,
                            'prepare' => new JsExpression('
                                    function prepare(query, settings) {
                                        settings.url += "?q=" + query;
                                        return settings;
                                    }
                                ')
                        ],
                        'limit' => 10,
                        'datumTokenizer' => "Bloodhound.tokenizers.obj.whitespace('value')",
                        'display' => 'value',
                        'templates' => [
                            'suggestion' => new JsExpression("Handlebars.compile('{$template}')")
                        ],
                    ],
                ],
            ]); ?>

            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info']) ?>

                <?= Html::a('Отключить отбор', ['/ferrymen'], ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
