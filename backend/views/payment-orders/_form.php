<?php

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

/* @var $this yii\web\View */
/* @var $model common\models\PaymentOrders */
/* @var $form yii\bootstrap\ActiveForm */

$formName = strtolower($model->formName());
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
            <?= $form->field($model, 'ferryman_id')->widget(Select2::className(), [
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
            ])->widget(MaskedInput::className(), [
                'clientOptions' => ['alias' =>  'numeric'],
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
        <div id="block-pd" class="col-md-3">
            <?php if ($dataSet != null): ?>
            <?= $this->render('_block_pd', [
                'model' => $model,
                'form' => $form,
                'dataSet' => $dataSet,
            ]); ?>

            <?php endif; ?>
        </div>
    </div>
    <?php if ($model->isNewRecord): ?>
    <p class="text-muted">Добавление файлов будет возможно после сохранения заявки. Нажмите &laquo;Создать&raquo;, система проверит веденные данные, если они будут корректны, то заявка будет сохранена, и Вы сможете добавить файлы.</p>
    <?php endif; ?>
    <?php if (!$model->isNewRecord && Yii::$app->user->can('root')): ?>
    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите причину отказа']) ?>

    <?php endif; ?>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Платежные ордеры', ['/payment-orders'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать черновик', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить черновик', ['class' => 'btn btn-primary btn-lg']) ?>

        <?= Html::submitButton('Сохранить и отправить на согласование <i class="fa fa-arrow-circle-right" aria-hidden="true"></i>', ['class' => 'btn btn-success btn-lg', 'name' => 'order_ready', 'title' => 'Создать и сразу отправить на согласование']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$url = Url::to(['/payment-orders/compose-pd-field']);

$this->registerJs(<<<JS

// Обработчик изменения перевозчика или способа расчетов с перевозчиком.
//
function composeFerrymanPaymentDestination() {
    ferryman_id = $("#$formName-ferryman_id").val();
    pd = $("input[name='PaymentOrders[pd_type]']:checked").val();
    $("#block-pd").load("$url?ferryman_id=" + ferryman_id + "&pd=" + pd);
} // composeFerrymanPaymentDestination()
JS
, yii\web\View::POS_BEGIN);

$this->registerJs(<<<JS
$("input[name='PaymentOrders[pd_type]']").on("change", composeFerrymanPaymentDestination);
JS
, yii\web\View::POS_READY);
?>
