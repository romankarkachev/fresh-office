<?php

use common\models\TransportTypes;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\typeahead\Typeahead;
use kartik\select2\Select2;
use common\models\Ferrymen;
use common\models\FerrymenTypes;
use common\models\PaymentConditions;

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
            <?= $form->field($model, 'searchEntire')->widget(Typeahead::className(), [
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

            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'state_id')->widget(Select2::className(), [
                        'data' => Ferrymen::arrayMapOfStatesForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'ft_id')->widget(Select2::className(), [
                        'data' => FerrymenTypes::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'pc_id')->widget(Select2::className(), [
                        'data' => PaymentConditions::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'tax_kind')->widget(Select2::className(), [
                        'data' => Ferrymen::arrayMapOfTaxKindsForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchTransportType')->widget(Select2::class, [
                        'data' => TransportTypes::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

            </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info']) ?>

                <?= Html::a('Отключить отбор', ['/ferrymen'], ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
