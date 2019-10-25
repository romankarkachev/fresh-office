<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use common\models\Units;
use common\models\DangerClasses;
use common\models\HandlingKinds;

/* @var $this yii\web\View */
/* @var $model common\models\DocumentsTp */
/* @var $counter integer */
/* @var $count integer */

// если производится создание новой заявки, то просто удаление строки
if ($model->isNewRecord) $delete_options = ['id' => 'btn-delete-row-'.$counter, 'class' => 'btn btn-danger btn-xs', 'data-counter' => $counter, 'title' => 'Удалить эту строку'];
// если происходит редактирование существующей завки, то кнопка дополняется подтверждением удаления и идентификатором удаляемой записи
else $delete_options = ['id' => 'btn-delete-row-'.$counter, 'class' => 'btn btn-danger btn-xs', 'data-counter' => $counter, 'data-id' => $model->id, 'title' => 'Удалить эту строку'];

if (!isset($count)) $count = 0;

$parentFormName = (new \common\models\Documents())->formName();
$formName = strtolower($model->formName());
?>

    <div class="row" id="dtp-row-<?= $counter ?>">
        <div class="col-md-6">
            <div class="col-md-9">
                <div class="form-group field-documentstp-name required">
                    <label class="control-label" for="documentstp-name"><?= $model->attributeLabels()['name'] ?></label>
                    <?= Html::input('text', $parentFormName . '[tp][' . $counter . '][name]', $model->name, ['class' => 'form-control input-sm', 'id' => 'documentstp-name-' . $counter, 'placeholder' => 'Введите наименование товара (услуги)']) ?>

                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group field-documents-fkko">
                    <label class="control-label" for="documents-fkko">ФККО</label>
                    <?= Html::input('text', $parentFormName . '[tp][' . $counter . '][fkko]', $model->fkkoName, ['class' => 'form-control input-sm', 'readonly' => true, 'id' => 'documentstp-fkko-'.$counter]) ?>

                    <p class="help-block help-block-error"></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="col-md-2">
                <div class="form-group field-<?= $formName ?>-unit_id required">
                    <label class="control-label" for="<?= $formName ?>-unit_id">Ед. изм.</label>
                    <?= Select2::widget([
                        'model' => $model,
                        'name' => $parentFormName . '[tp][' . $counter . '][unit_id]',
                        'value' => $model->unit_id,
                        'initValueText' => $model->unitName,
                        'data' => Units::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'size' => Select2::SMALL,
                        'hideSearch' => true,
                        'language' => 'ru',
                        'options' => [
                            'id' => $formName . '-unit_id-' . $counter,
                            'data-counter' => $counter,
                            'placeholder' => '- выберите -',
                            'title' => !empty($model->src_unit) ? 'Единица измерения из источника: ' . $model->src_unit : '',
                        ],
                    ]) ?>

                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group field-<?= $formName ?>-dc_id required">
                    <label class="control-label" for="<?= $formName ?>-dc_id">Кл. опасн.</label>
                    <?= Select2::widget([
                        'model' => $model,
                        'name' => $parentFormName . '[tp][' . $counter . '][dc_id]',
                        'value' => $model->dc_id,
                        'initValueText' => $model->dcName,
                        'data' => DangerClasses::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'size' => Select2::SMALL,
                        'hideSearch' => true,
                        'language' => 'ru',
                        'options' => [
                            'id' => $formName . '-dc_id-' . $counter,
                            'data-counter' => $counter,
                            'placeholder' => '- выберите -',
                            'title' => !empty($model->src_dc) ? 'Класс опасности из источника: ' . $model->src_dc : '',
                        ],
                    ]) ?>

                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group field-<?= $formName ?>-hk_id required">
                    <label class="control-label" for="<?= $formName ?>-hk_id">Вид обращ.</label>
                    <?= Select2::widget([
                        'model' => $model,
                        'name' => $parentFormName . '[tp][' . $counter . '][hk_id]',
                        'value' => $model->hk_id,
                        'initValueText' => $model->hkName,
                        'data' => HandlingKinds::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'size' => Select2::SMALL,
                        'hideSearch' => true,
                        'language' => 'ru',
                        'options' => [
                            'id' => $formName . '-hk_id-' . $counter,
                            'data-counter' => $counter,
                            'placeholder' => '- выберите -',
                            'title' => !empty($model->src_uw) ? 'Вид обращения из источника: ' . $model->src_uw : '',
                        ],
                    ]) ?>

                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group field-documents-quantity required">
                    <label class="control-label" for="documents-quantity"><?= $model->attributeLabels()['quantity'] ?></label>
                    <?= Html::input('text', $parentFormName . '[tp][' . $counter . '][quantity]', $model->quantity, ['class' => 'form-control input-sm', 'placeholder' => 'Введите']) ?>

                    <p class="help-block help-block-error"></p>
                </div>
            </div>
            <div class="col-md-1">
                <label class="control-label" for="<?= 'btn-delete-row-' . $counter ?>">&nbsp;</label>
                <div class="form-group">
                    <?= Html::a('<i class="fa fa-minus" aria-hidden="true"></i>', '#', $delete_options) ?>

                </div>
            </div>
        </div>
    </div>
