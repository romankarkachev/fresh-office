<?php

use common\models\PostDeliveryKinds;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;
use kartik\select2\Select2;
use common\models\Ferrymen;
use common\models\FerrymenBankDetails;
use common\models\FerrymenBankCards;
use common\models\PaymentOrders;
use common\models\CorrespondencePackages;

/* @var $this yii\web\View */
/* @var $model common\models\PaymentOrders */
/* @var $form yii\bootstrap\ActiveForm */

$formNameId = strtolower($model->formName());
$pd = PaymentOrders::fetchPaymentDestinations();

$dataSet = null;
switch ($model->pd_type) {
    case PaymentOrders::PAYMENT_DESTINATION_ACCOUNT:
        $dataSet = FerrymenBankDetails::arrayMapForSelect2($model->ferryman_id);
        break;
    case PaymentOrders::PAYMENT_DESTINATION_CARD:
        $dataSet = FerrymenBankCards::arrayMapForSelect2($model->ferryman_id);
        break;
}
?>

<div class="payment-orders-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'ferryman_id')->widget(Select2::class, [
                'data' => Ferrymen::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'pluginEvents' => [
                    'change' => 'function() { composeFerrymanPaymentDestination() }',
                ],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'projects')->textInput(['placeholder' => 'Проекты через запятую']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'amount', [
                'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
            ])->widget(MaskedInput::class, [
                'clientOptions' => [
                    'alias' =>  'numeric',
                    'groupSeparator' => ' ',
                    'autoUnmask' => true,
                    'autoGroup' => true,
                    'removeMaskOnSubmit' => true,
                ],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => '0',
            ]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'pd_type', [
                'inline' => true,
            ])->radioList(ArrayHelper::map($pd, 'id', 'name'), [
                'class' => 'btn-group',
                'data-toggle' => 'buttons',
                'unselect' => null,
                'item' => function ($index, $label, $name, $checked, $value) use ($pd) {
                    $hint = '';
                    $key = array_search($value, array_column($pd, 'id'));
                    if ($key !== false && isset($pd[$key]['hint'])) $hint = ' title="' . $pd[$key]['hint'] . '"';

                    return '<label class="btn btn-default' . ($checked ? ' active' : '') . '"' . $hint . '>' .
                        Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn']) . $label . '</label>';
                },
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'imt_num', ['template' => CorrespondencePackages::FORM_FIELD_TRACK_TEMPLATE])
                ->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Введите идентификатор отправления',
                    'title' => 'Введите идентификатор отправления',
                    'disabled' => !empty($model->imt_num),
                ]) ?>

        </div>
    </div>
    <?php if ($model->isNewRecord): ?>
    <div class="form-group">
        <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите комментарий']) ?>

    </div>
    <?php endif; ?>
    <div id="block-pd">
        <?php if (!empty($dataSet) || (!empty($model->ferryman) && !empty($model->ferryman->ati_code))): ?>
        <?= $this->render('_block_pd', [
            'model' => $model,
            'form' => $form,
            'dataSet' => $dataSet,
        ]); ?>

        <?php endif; ?>
    </div>
    <?php if ($model->isNewRecord): ?>
    <p class="text-muted">Добавление файлов будет возможно после сохранения заявки. Нажмите &laquo;Создать&raquo;, система проверит веденные данные, если они будут корректны, то заявка будет сохранена, и Вы сможете добавить файлы.</p>
    <?php elseif (Yii::$app->user->can('root')): ?>
    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите причину отказа']) ?>

    <?php endif; ?>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Платежные ордеры', (!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['/payment-orders']), ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать черновик', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить черновик', ['class' => 'btn btn-primary btn-lg']) ?>

        <?= Html::submitButton('Сохранить и отправить на согласование <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>', ['class' => 'btn btn-success btn-lg', 'name' => 'order_ready', 'title' => 'Создать и сразу отправить на согласование']) ?>
        <?php endif; ?>

        <?php if (!$model->isNewRecord): ?>
        <?= Html::a('<i class="fa fa-check text-success" aria-hidden="true"></i> АВР', '#', ['id' => 'btnCcr', 'class' => 'btn btn-lg btn-default', 'title' => 'Установить пометку о том, что акт выполненных работ получен']) ?>

        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
<div id="modalWindow" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header"><h4 id="modalTitle" class="modal-title">Modal title</h4></div>
            <div id="modalBody" class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<?php
$url = Url::to(['/payment-orders/compose-pd-field']);
$urlCcProvided = Url::to(\backend\controllers\PaymentOrdersController::URL_SET_CCP_ON_THE_FLY_AS_ARRAY);
$urlTrackByNumber = Url::to(['/tracking/track-by-number']);

$pdkRu = PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ;
$this->registerJs(<<<JS

// Обработчик изменения перевозчика или способа расчетов с перевозчиком.
//
function composeFerrymanPaymentDestination() {
    url = "$url";
    ferryman_id = $("#$formNameId-ferryman_id").val();
    pd = $("input[name='PaymentOrders[pd_type]']:checked").val();
    if (ferryman_id || pd) {
        if (ferryman_id) {
            url += "?ferryman_id=" + ferryman_id;
        }
        if (pd) {
            if (ferryman_id) {
                url += "&";
            }
            else {
                url += "?";
            }
            url += "pd=" + pd;
        }
    }
    \$block = $("#block-pd");
    \$block.html('<p class="text-center"><i class="fa fa-spinner fa-pulse fa-fw text-primary text-muted"></i><span class="sr-only">Подождите...</span></p>');
    \$block.load(url);
} // composeFerrymanPaymentDestination()
JS
, yii\web\View::POS_BEGIN);

$imTrackNumberId = Html::getInputId($model, 'imt_num');
$this->registerJs(<<<JS

// Обработчик щелчка по кнопкам, позволяющим отметить признак наличия актов выполненных работ.
//
function btnSetCcProvidedOnTheFlyOnClick() {
    \$button = $(this);
    id = $(this).attr("data-id");
    if (id) {
        $.post("$urlCcProvided?id=" + id, function(data) {
            var response = jQuery.parseJSON(data);
            if (response != false) {
                \$button.replaceWith('<i class="fa fa-check text-success"></i>');
            }
            else
                \$button.replaceWith('<i class="fa fa-times text-danger"></i>');
        });
    }

    return false;
} // btnSetCcProvidedOnTheFlyOnClick()

// Обработчик щелчка по кнопке "Отследить".
//
function btnTrackNumberOnClick() {
    tracknum = $("#$imTrackNumberId").val();
    if (tracknum) {
        \$body = $("#modalBody");
        $("#modalTitle").text("Отслеживание");
        \$body.html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#modalWindow").modal();
        \$body.load("$urlTrackByNumber?pd_id=$pdkRu&track_num=" + tracknum);
    }

    return false;
} // btnTrackNumberOnClick()

$("input[name='PaymentOrders[pd_type]']").on("change", composeFerrymanPaymentDestination);
$(document).on("click", "a[id ^= 'btnCcr']", btnSetCcProvidedOnTheFlyOnClick);
$(document).on("click", "#btnTrackNumber", btnTrackNumberOnClick);
JS
, yii\web\View::POS_READY);
?>
