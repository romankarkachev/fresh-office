<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use common\models\DocumentsTp;

/* @var $this yii\web\View */
/* @var $form ActiveForm */
/* @var $model common\models\DocumentsTp */
/* @var $document common\models\Documents */
/* @var $counter integer */
/* @var $count integer */

// если производится создание новой заявки, то просто удаление строки
if ($document->isNewRecord) $delete_options = ['id' => 'btn-delete-row-'.$counter, 'class' => 'btn btn-danger btn-xs', 'data-counter' => $counter, 'title' => 'Удалить эту строку'];
// если происходит редактирование существующей завки, то кнопка дополняется подтверждением удаления и идентификатором удаляемой записи
else $delete_options = ['id' => 'btn-delete-row-'.$counter, 'class' => 'btn btn-danger btn-xs', 'data-counter' => $counter, 'data-id' => $model->id, 'title' => 'Удалить эту строку'];

if (!isset($count)) $count = 0;
?>

    <div class="row" id="dtp-row-<?= $counter ?>">
        <div class="col-md-6">
            <div class="form-group field-documents-product_id required">
                <label class="control-label" for="documents-product_id"><?= $model->attributeLabels()['product_id'] ?></label>
                <?= Select2::widget([
                    'model' => $model,
                    'name' => 'Documents[tp]['.$counter.'][product_id]',
                    'value' => $model->product_id,
                    'initValueText' => $model->productName,
                    //'theme' => Select2::THEME_BOOTSTRAP,
                    'language' => 'ru',
                    'options' => [
                        'id' => 'documentstp-product_id-'.$counter,
                        'data-counter' => $counter,
                        'placeholder' => 'Введите наименование'
                    ],
                    'pluginOptions' => [
                        'minimumInputLength' => 1,
                        'language' => 'ru',
                        'ajax' => [
                            'url' => Url::to(['products/list-nf']),
                            'delay' => 250,
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term, counter: $(this).attr("data-counter")}; }')
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(result) { return result.text; }'),
                        'templateSelection' => new JsExpression('function (result) {
if (!result.id) {return result.text;}
if (result.fkko != "") $("#documentstp-fkko-" + result.counter).val(result.fkko);
if (result.unit != "") $("#documentstp-unit-" + result.counter).val(result.unit);

return result.text;
}'),
                    ],
                ]) ?>

                <p class="help-block help-block-error"></p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group field-documents-fkko">
                <label class="control-label" for="documents-fkko">ФККО</label>
                <?= Html::input('text', 'Documents[tp]['.$counter.'][fkko]', $model->product->fkko, ['class' => 'form-control', 'readonly' => true, 'id' => 'documentstp-fkko-'.$counter]) ?>

                <p class="help-block help-block-error"></p>
            </div>
        </div>
        <div class="col-md-1">
            <div class="form-group field-documents-unit">
                <label class="control-label" for="documents-unit">Ед. изм.</label>
                <?= Html::input('text', 'Documents[tp]['.$counter.'][unit]', $model->product->unit, ['class' => 'form-control', 'readonly' => true, 'id' => 'documentstp-unit-'.$counter]) ?>

                <p class="help-block help-block-error"></p>
            </div>
        </div>
        <div class="col-md-1">
            <div class="form-group field-documents-quantity required">
                <label class="control-label" for="documents-quantity"><?= $model->attributeLabels()['quantity'] ?></label>
                <?= Html::input('text', 'Documents[tp]['.$counter.'][quantity]', $model->quantity, ['class' => 'form-control']) ?>

                <p class="help-block help-block-error"></p>
            </div>
        </div>
        <?php if ($counter == 0): ?>
        <div class="col-md-2">
            <label class="control-label" for="btn-add-row">&nbsp;</label>
            <div class="form-group">
                <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i> Добавить строку', '#', ['id' => 'btn-add-row', 'class' => 'btn btn-default', 'data-count' => $count]) ?>

            </div>
        </div>
        <?php endif; ?>
        <?php if ($counter > 0): ?>
        <div class="col-md-2">
            <label class="control-label" for="<?= 'btn-delete-row-'.$counter ?>">&nbsp;</label>
            <div class="form-group">
                <?= Html::a('<i class="fa fa-minus" aria-hidden="true"></i>', '#', $delete_options) ?>

            </div>
        </div>
        <?php endif; ?>
    </div>
