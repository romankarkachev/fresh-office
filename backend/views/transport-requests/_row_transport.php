<?php

use yii\helpers\Html;
use yii\widgets\MaskedInput;
use kartik\select2\Select2;
use common\models\TransportTypes;

/* @var $this yii\web\View */
/* @var $tr common\models\TransportRequests */
/* @var $model common\models\TransportRequestsTransport */
/* @var $form yii\bootstrap\ActiveForm */

$trFormName = $tr->formName();
$formName = strtolower($model->formName());

// если производится создание объекта, то просто удаление строки
$delete_options = ['id' => 'btnDeleteTransportRow-'.$counter, 'class' => 'btn btn-danger btn-xs', 'data-counter' => $counter, 'title' => 'Удалить эту строку'];
if (!$model->isNewRecord)
    // если происходит редактирование существующего объекта, то кнопка дополняется подтверждением удаления и идентификатором удаляемой записи
    $delete_options['data-id'] = $model->id;
?>

    <div class="row" id="transport-row-<?= $counter ?>">
        <div class="col-md-2">
            <div class="form-group field-<?= $formName ?>-tt_id required">
                <label class="control-label" for="<?= $formName ?>-tt_id"><?= $model->getAttributeLabel('tt_id') ?></label>
                <?= Select2::widget([
                    'model' => $model,
                    'name' => $trFormName . '[tpTransport]['.$counter.'][tt_id]',
                    'value' => $model->tt_id,
                    'initValueText' => $model->ttName,
                    'data' => TransportTypes::arrayMapForSelect2(),
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'size' => Select2::SMALL,
                    'hideSearch' => true,
                    'language' => 'ru',
                    'options' => [
                        'id' => $trFormName . '-tt_id-'.$counter,
                        'data-counter' => $counter,
                        'placeholder' => '- выберите -'
                    ],
                ]) ?>

                <p class="help-block help-block-error"></p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group field-<?= $formName ?>-amount">
                <label class="control-label" for="<?= $formName ?>-amount"><?= $model->getAttributeLabel('amount') ?></label>
                <div class="input-group">
                    <?= MaskedInput::widget([
                        'name' => $trFormName . '[tpTransport]['.$counter.'][amount]',
                        'value' => $model->amount,
                        'options' => [
                            'id' => $formName . '-amount-'.$counter,
                            'class' => 'form-control input-sm',
                            'placeholder' => '0',
                            'title' => 'Стоимость за весь объем по этому типу техники',
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
            <label class="control-label" for="<?= 'btnDeleteTransportRow-' . $counter ?>">&nbsp;</label>
            <div class="form-group">
                <?= Html::a('<i class="fa fa-minus" aria-hidden="true"></i>', '#', $delete_options) ?>

            </div>
        </div>
    </div>
