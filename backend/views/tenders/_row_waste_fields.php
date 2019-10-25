<?php

use kartik\typeahead\Typeahead;
use common\models\TendersTp;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model common\models\TendersTp */
/* @var $parentModel common\models\Tenders */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $counter integer счетчик добавленных отходов */

$formNameId = strtolower($model->formName());
$tenderFormName = $parentModel->formName();
?>

<div class="row" id="<?= TendersTp::DOM_IDS['ROW_ID'] ?>-<?= $counter ?>">
    <div class="col-md-6">
        <?= $form->field($model, 'fkko_name')->widget(Typeahead::class, [
            'options' => [
                'id' => $formNameId . '-fkko_name-' . $counter,
                'name' => $tenderFormName . '[crudeWaste][' . $counter . '][fkko_name]',
                'value' => $model->fkko_name,
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
    $("#' . $formNameId . '-fkko_id-' . $counter .'").val(suggestion.id); // идентификатор ФККО
}
                            ',
                'typeahead:asyncreceive' => '
function() {
    $("#' . $formNameId . '-fkko_id-' . $counter .'").val("");
}
                            ',
            ],
        ]) ?>

    </div>
    <div class="col-md-1">
        <label class="control-label" for="<?= TendersTp::DOM_IDS['DELETE_BUTTON'] . '-' . $counter ?>">&nbsp;</label>
        <div class="form-group">
            <?= \yii\helpers\Html::a('<i class="fa fa-trash-o"></i>', '#', [
                'id' => TendersTp::DOM_IDS['DELETE_BUTTON'] . '-' . $counter,
                'class' => 'btn btn-danger btn-xs',
                'data-counter' => $counter,
                'title' => 'Удалить эту строку ',
            ]) ?>

        </div>
    </div>
    <?= $form->field($model, 'fkko_id')->hiddenInput([
        'id' => $formNameId . '-fkko_id-' . $counter,
        'name' => $tenderFormName . '[crudeWaste][' . $counter . '][fkko_id]',
    ])->label(false) ?>

</div>
