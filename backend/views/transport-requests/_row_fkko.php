<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\MaskedInput;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\typeahead\Typeahead;
use common\models\DangerClasses;
use common\models\AggregateStates;
use common\models\Units;

/* @var $this yii\web\View */
/* @var $tr common\models\TransportRequests */
/* @var $model common\models\TransportRequestsWaste */
/* @var $form yii\bootstrap\ActiveForm */

$trFormName = $tr->formName();
$formName = strtolower($model->formName());

// если производится создание объекта, то просто удаление строки
$delete_options = ['id' => 'btnDeleteFkkoRow-'.$counter, 'class' => 'btn btn-danger btn-xs', 'data-counter' => $counter, 'title' => 'Удалить эту строку'];
if (!$model->isNewRecord)
    // если происходит редактирование существующего объекта, то кнопка дополняется подтверждением удаления и идентификатором удаляемой записи
    $delete_options['data-id'] = $model->id;
?>

    <div class="row" id="fkko-row-<?= $counter ?>">
        <div class="col-md-4">
            <div class="col-md-12">
                <div class="form-group field-<?= $formName ?>-fkko_name required">
                    <label class="control-label" for="<?= $formName ?>-fkko_name"><?= $model->getAttributeLabel('fkko_name') ?></label>
                    <?= Typeahead::widget([
                        'model' => $model,
                        'name' => $trFormName . '[tpWaste]['.$counter.'][fkko_name]',
                        'value' => $model->fkko_name,
                        'options' => [
                            'id' => $formName . '-newNomenclature-' . $counter,
                            'class' => 'form-control input-sm',
                            'data-counter' => $counter,
                            'placeholder' => 'Код ФККО или наименование',
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
                                            settings.url += "?q=" + query + "&counter=' . $counter .'";
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
                                    $("#' . $formName . '-fkko_id-' . $counter .'").val(suggestion.id); // идентификатор ФККО
                                }
                            ',
                            'typeahead:asyncreceive' => 'function() {
                                $("#' . $formName . '-fkko_id-' . $counter .'").val(null); // идентификатор услуги
                            }
                            ',
                        ],
                    ]); ?>

                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="col-md-2">
                <div class="form-group field-<?= $formName ?>-dc_id required">
                    <label class="control-label" for="<?= $formName ?>-dc_id"><?= $model->getAttributeLabel('dc_id') ?></label>
                    <?= Select2::widget([
                        'model' => $model,
                        'name' => $trFormName . '[tpWaste]['.$counter.'][dc_id]',
                        'value' => $model->dc_id,
                        'initValueText' => $model->dcName,
                        'data' => DangerClasses::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'size' => Select2::SMALL,
                        'hideSearch' => true,
                        'language' => 'ru',
                        'options' => [
                            'id' => $trFormName . '-dc_id-'.$counter,
                            'data-counter' => $counter,
                            'placeholder' => '- выберите -'
                        ],
                    ]) ?>

                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group field-<?= $formName ?>-newPacktingType">
                    <label class="control-label" for="<?= $formName ?>-newPacktingType"><?= $model->getAttributeLabel('packing_id') ?></label>
                    <?= Typeahead::widget([
                        'model' => $model,
                        'name' => $trFormName . '[tpWaste]['.$counter.'][newPacktingType]',
                        'value' => $model->packing != null ? $model->packing->name : '',
                        'options' => [
                            'id' => $formName . '-newPacktingType-' . $counter,
                            'class' => 'form-control input-sm',
                            'data-counter' => $counter,
                            'placeholder' => 'Введите вид упаковки',
                        ],
                        'scrollable' => true,
                        'pluginOptions' => ['highlight' => true],
                        'dataset' => [
                            [
                                'remote' => [
                                    'url' => Url::to(['transport-requests/list-of-packing-types-for-typeahead']),
                                    'rateLimitWait' => 500,
                                    'prepare' => new JsExpression('
                                        function prepare(query, settings) {
                                            settings.url += "?q=" + query + "&counter=' . $counter .'";
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
                                    $("#' . $formName . '-packing_id-' . $counter . '").val(suggestion.id); // тип услуги
                                }
                            ',
                            'typeahead:asyncreceive' => 'function() {
                                $("#' . $formName . '-packing_id-' . $counter .'").val(null); // тип услуги
                            }
                            ',
                        ],
                    ]); ?>

                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group field-<?= $formName ?>-ags_id required">
                    <label class="control-label" for="<?= $formName ?>-ags_id"><?= $model->getAttributeLabel('ags_id') ?></label>
                    <?= Select2::widget([
                        'model' => $model,
                        'name' => $trFormName . '[tpWaste]['.$counter.'][ags_id]',
                        'value' => $model->ags_id,
                        'initValueText' => $model->dcName,
                        'data' => AggregateStates::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'size' => Select2::SMALL,
                        'hideSearch' => true,
                        'language' => 'ru',
                        'options' => [
                            'id' => $trFormName . '-ags_id-'.$counter,
                            'data-counter' => $counter,
                            'placeholder' => '- выберите -'
                        ],
                    ]) ?>

                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group field-<?= $formName ?>-unit_id required">
                    <label class="control-label" for="<?= $formName ?>-unit_id"><?= $model->getAttributeLabel('unit_id') ?></label>
                    <?= Select2::widget([
                        'model' => $model,
                        'name' => $trFormName . '[tpWaste]['.$counter.'][unit_id]',
                        'value' => $model->unit_id,
                        'initValueText' => $model->dcName,
                        'data' => Units::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'size' => Select2::SMALL,
                        'hideSearch' => true,
                        'language' => 'ru',
                        'options' => [
                            'id' => $trFormName . '-unit_id-'.$counter,
                            'data-counter' => $counter,
                            'placeholder' => '- выберите -'
                        ],
                    ]) ?>

                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group field-<?= $formName ?>-measure">
                    <label class="control-label" for="<?= $formName ?>-measure"><?= $model->getAttributeLabel('measure') ?></label>
                    <div class="input-group">
                        <?= MaskedInput::widget([
                            'name' => $trFormName . '[tpWaste]['.$counter.'][measure]',
                            'value' => $model->measure,
                            'options' => [
                                'id' => $formName . '-measure-'.$counter,
                                'class' => 'form-control input-sm',
                                'placeholder' => '0',
                                'title' => 'Объем, количество, мера измерения отходов'
                            ],
                            'clientOptions' => [
                                'alias' =>  'numeric',
                                'digitsOptional' => true,
                                'radixPoint' => '.',
                                'groupSeparator' => '',
                                'autoGroup' => true,
                                'removeMaskOnSubmit' => true,
                            ],
                        ]) ?>

                        <span class="input-group-addon"><i class="fa fa-rub" aria-hidden="true"></i></span></div>
                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-md-1">
                <label class="control-label" for="<?= 'btnDeleteFkkoRow-' . $counter ?>">&nbsp;</label>
                <div class="form-group">
                    <?= Html::a('<i class="fa fa-minus" aria-hidden="true"></i>', '#', $delete_options) ?>

                </div>
            </div>
        </div>
        <?= Html::input('hidden', $trFormName . '[tpWaste]['.$counter.'][fkko_id]', $model->fkko_id, [
            'id' => $formName . '-fkko_id-' . $counter,
        ]) ?>

        <?= Html::input('hidden', $trFormName . '[tpWaste]['.$counter.'][packing_id]', $model->packing_id, [
            'id' => $formName . '-packing_id-' . $counter,
        ]) ?>

    </div>
