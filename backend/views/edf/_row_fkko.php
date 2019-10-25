<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\MaskedInput;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\typeahead\Typeahead;
use common\models\HandlingKinds;
use common\models\Units;

/* @var $this yii\web\View */
/* @var $edf common\models\Edf */
/* @var $model common\models\EdfTp */
/* @var $form yii\bootstrap\ActiveForm */

$edfFormName = $edf->formName();
$formName = strtolower($model->formName());

// если производится создание объекта, то просто удаление строки
$delete_options = ['id' => 'btnDeleteFkkoRow-'.$counter, 'class' => 'btn btn-danger btn-xs', 'data-counter' => $counter, 'title' => 'Удалить эту строку'];
if (!$model->isNewRecord)
    // если происходит редактирование существующего объекта, то кнопка дополняется подтверждением удаления и идентификатором удаляемой записи
    $delete_options['data-id'] = $model->id;
?>

    <div class="row" id="fkko-row-<?= $counter ?>">
        <div class="col-md-4">
            <div class="col-md-1">
                <label for="<?= $formName . '-is_addon_required-' . $counter ?>" class="control-label">&nbsp;</label>
                <div class="form-group" title="При экспорте товаров во Fresh Office добавлять в наименование &laquo;Оказание услуг по обращению с отходом&raquo;">
                    <div class="checkbox" style="margin-top:5px;">
                        <?= Html::input('checkbox', $edfFormName . '[tp][' . $counter . '][is_addon_required]', $model->id, ['id' => $formName . '-is_addon_required-' . $counter, 'checked' => false]) ?>

                    </div>
                </div>
            </div>
            <div class="col-md-11">
                <div class="form-group field-<?= $formName ?>-fkko_name required">
                    <label class="control-label" for="<?= $formName ?>-fkko_name"><?= $model->getAttributeLabel('fkko_name') ?> *</label>
                    <?= Typeahead::widget([
                        'model' => $model,
                        'name' => $edfFormName . '[tp]['.$counter.'][fkko_name]',
                        'value' => $model->fkko_name,
                        'options' => [
                            'id' => $formName . '-newNomenclature-' . $counter,
                            'class' => 'form-control input-sm',
                            'data-counter' => $counter,
                            'placeholder' => 'Код ФККО или наименование',
                            'autocomplete' => 'off',
                        ],
                        'scrollable' => true,
                        'pluginOptions' => ['highlight' => true],
                        'dataset' => [
                            [
                                'remote' => [
                                    'url' => Url::to(\backend\controllers\EdfController::FKKO_LIST_FOR_TYPEAHEAD_URL_AS_ARRAY),
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
                                    if (suggestion.dc_id) $("#' . $formName . '-dc_id-' . $counter .'").val(suggestion.dc_id).trigger("change");
                                    fkkoOnChange(suggestion.id, ' . $counter . ');
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
                        'name' => $edfFormName . '[tp]['.$counter.'][dc_id]',
                        'value' => $model->dc_id,
                        'initValueText' => $model->dcName,
                        'data' => \common\models\DangerClasses::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'size' => Select2::SMALL,
                        'hideSearch' => true,
                        'language' => 'ru',
                        'options' => [
                            'id' => $formName . '-dc_id-'.$counter,
                            'data-counter' => $counter,
                            'placeholder' => '- выберите -'
                        ],
                    ]) ?>

                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group field-<?= $formName ?>-unit_id required">
                    <label class="control-label" for="<?= $formName ?>-unit_id">Ед. изм.</label>
                    <?= Select2::widget([
                        'model' => $model,
                        'name' => $edfFormName . '[tp]['.$counter.'][unit_id]',
                        'value' => $model->unit_id,
                        'initValueText' => $model->unitName,
                        'data' => Units::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'size' => Select2::SMALL,
                        'hideSearch' => true,
                        'language' => 'ru',
                        'options' => [
                            'id' => $formName . '-unit_id-'.$counter,
                            'data-counter' => $counter,
                            'placeholder' => '- выберите -'
                        ],
                    ]) ?>

                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group field-<?= $formName ?>-hk_id required">
                    <label class="control-label" for="<?= $formName ?>-hk_id"><?= $model->getAttributeLabel('hk_id') ?> *</label>
                    <?= Select2::widget([
                        'model' => $model,
                        'name' => $edfFormName . '[tp]['.$counter.'][hk_id]',
                        'value' => $model->hk_id,
                        'initValueText' => $model->hkName,
                        'data' => HandlingKinds::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'size' => Select2::SMALL,
                        'hideSearch' => true,
                        'language' => 'ru',
                        'options' => [
                            'id' => $formName . '-hk_id-'.$counter,
                            'data-counter' => $counter,
                            'placeholder' => '- выберите -'
                        ],
                    ]) ?>

                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group field-<?= $formName ?>-measure">
                    <label class="control-label" for="<?= $formName ?>-measure"><?= $model->getAttributeLabel('measure') ?></label>
                    <?= MaskedInput::widget([
                        'name' => $edfFormName . '[tp]['.$counter.'][measure]',
                        'value' => $model->measure,
                        'options' => [
                            'id' => $formName . '-measure-'.$counter,
                            'class' => 'form-control input-sm',
                            'placeholder' => '0',
                            'title' => 'Объем, количество, мера измерения отходов',
                            'data-num' => $counter,
                        ],
                        'clientOptions' => [
                            'alias' =>  'decimal',
                            'digits' => 3,
                            'digitsOptional' => true,
                            'radixPoint' => '.',
                            'groupSeparator' => ' ',
                            'autoGroup' => true,
                            'removeMaskOnSubmit' => true,
                        ],
                    ]) ?>

                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group field-<?= $formName ?>-price">
                    <label class="control-label" for="<?= $formName ?>-price"><?= $model->getAttributeLabel('price') ?></label>
                    <?= MaskedInput::widget([
                        'name' => $edfFormName . '[tp]['.$counter.'][price]',
                        'value' => $model->price,
                        'options' => [
                            'id' => $formName . '-price-'.$counter,
                            'class' => 'form-control input-sm',
                            'placeholder' => '0',
                            'title' => 'Цена за единицу',
                            'data-num' => $counter,
                        ],
                        'clientOptions' => [
                            'alias' =>  'decimal',
                            'digits' => 2,
                            'digitsOptional' => true,
                            'radixPoint' => '.',
                            'groupSeparator' => ' ',
                            'autoGroup' => true,
                            'removeMaskOnSubmit' => true,
                        ],
                    ]) ?>

                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group field-<?= $formName ?>-amount">
                    <label class="control-label" for="<?= $formName ?>-amount"><?= $model->getAttributeLabel('amount') ?></label>
                    <?= MaskedInput::widget([
                        'name' => $edfFormName . '[tp]['.$counter.'][amount]',
                        'value' => $model->amount,
                        'options' => [
                            'id' => $formName . '-amount-' . $counter,
                            'class' => 'form-control input-sm',
                            'placeholder' => '0',
                            'title' => 'Общая стоимость',
                        ],
                        'clientOptions' => [
                            'alias' =>  'decimal',
                            'digits' => 2,
                            'digitsOptional' => true,
                            'radixPoint' => '.',
                            'groupSeparator' => ' ',
                            'autoGroup' => true,
                            'removeMaskOnSubmit' => true,
                        ],
                    ]) ?>

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
        <?= Html::input('hidden', $edfFormName . '[tp]['.$counter.'][fkko_id]', $model->fkko_id, [
            'id' => $formName . '-fkko_id-' . $counter,
        ]) ?>

    </div>
<?php
$this->registerJs(<<<JS
$("input[type='checkbox']").iCheck({checkboxClass: "icheckbox_square-green"});
JS
, \yii\web\View::POS_READY);
?>