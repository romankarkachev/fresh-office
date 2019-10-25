<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\typeahead\Typeahead;

/* @var $this yii\web\View */
/* @var $tender common\models\Tenders */
/* @var $model common\models\TendersTp */
/* @var $form yii\bootstrap\ActiveForm */

$tenderFormName = $tender->formName();
$formName = strtolower($model->formName());

// если производится создание объекта, то просто удаление строки
$delete_options = ['id' => 'btnDeleteFkkoRow-' . $counter, 'class' => 'btn btn-danger btn-xs', 'data-counter' => $counter, 'title' => 'Удалить эту строку'];
if (!$model->isNewRecord)
    // если происходит редактирование существующего объекта, то кнопка дополняется подтверждением удаления и идентификатором удаляемой записи
    $delete_options['data-id'] = $model->id;
?>

    <div class="row" id="fkko-row-<?= $counter ?>" data-counter="<?= $counter ?>">
        <div class="col-md-4">
            <div class="col-md-12">
                <div class="form-group field-<?= $formName ?>-fkko_name required">
                    <label class="control-label" for="<?= $formName ?>-fkko_name"><?= $model->getAttributeLabel('fkko_name') ?> *</label>
                    <?= Typeahead::widget([
                        'model' => $model,
                        'name' => $tenderFormName . '[tp]['.$counter.'][fkko_name]',
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
            <div class="col-md-1">
                <label class="control-label" for="<?= 'btnDeleteFkkoRow-' . $counter ?>">&nbsp;</label>
                <div class="form-group">
                    <?= Html::a('<i class="fa fa-minus" aria-hidden="true"></i>', '#', $delete_options) ?>

                </div>
            </div>
        </div>
        <?= Html::input('hidden', $tenderFormName . '[tp]['.$counter.'][fkko_id]', $model->fkko_id, [
            'id' => $formName . '-fkko_id-' . $counter,
        ]) ?>

    </div>
