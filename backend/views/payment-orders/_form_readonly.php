<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datecontrol\DateControl;
use common\models\FerrymenBankDetails;
use common\models\FerrymenBankCards;
use common\models\PaymentOrders;
use common\models\PaymentOrdersStates;

/* @var $this yii\web\View */
/* @var $model common\models\PaymentOrders */
/* @var $form yii\bootstrap\ActiveForm */

$paymentDetails = '';
if ($model->pd_type != null && $model->pd_id != null) {
    $account = '';
    switch ($model->pd_type) {
        case PaymentOrders::PAYMENT_DESTINATION_ACCOUNT:
            $paymentDestination = FerrymenBankDetails::findOne($model->pd_id);
            if ($paymentDestination != null) $account = ' номер ' . $paymentDestination->bank_an . ' в ' . $paymentDestination->bank_name;
            break;
        case PaymentOrders::PAYMENT_DESTINATION_CARD:
            $paymentDestination = FerrymenBankCards::findOne($model->pd_id);
            if ($paymentDestination != null) $account = ' номер ' . $paymentDestination->number . ($paymentDestination->bank != null ? ' в банке ' . $paymentDestination->bank : '');
            break;
    }

    if ($account != '') $paymentDetails = 'Для оплаты выбран способ <strong>' . $model->pdTypeName . '</strong>' . $account . '.';
}

$buttonToDraft = Html::submitButton('Вернуть в черновики', ['class' => 'btn btn-default btn-lg', 'name' => 'order_repeat', 'title' => 'Сделать черновиком']);
?>

<div class="payment-orders-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="form-group">
        <p class="lead">
            Пользователь
            <?= $model->createdByProfileName ?>
            запросил оплату перевозчику
            <strong><?= $model->ferrymanName?></strong>
            по факту завершения проекта(ов): <?= str_replace(',', ', ', $model->projects) ?>.
            <?= $paymentDetails ?>
        </p>
        <p class="lead">Текущий статус: <strong><?= $model->stateName ?></strong>.</p>
    </div>
    <?php if ((Yii::$app->user->can('accountant') || Yii::$app->user->can('accountant_b')) && (in_array($model->state_id, PaymentOrdersStates::PAYMENT_STATES_SET_RECORD_CONFIRMED))): ?>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'payment_date')->widget(DateControl::className(), [
                'value' => $model->payment_date,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => [
                        'placeholder' => 'Выберите дату оплаты',
                    ],
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ]) ?>

        </div>
    </div>
    <?php endif; ?>
    <?php if ($model->state_id == PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН): ?>
    <?= $form->field($model, 'fileCc')->fileInput(); ?>

    <?php endif; ?>
    <?php if ((Yii::$app->user->can('root') || Yii::$app->user->can('accountant') || Yii::$app->user->can('accountant_b')) && (in_array($model->state_id, PaymentOrdersStates::PAYMENT_STATES_SET_RECORD_CONFIRMED))): ?>
    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите причину отказа']) ?>

    <?php endif; ?>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Платежные ордеры', ['/payment-orders'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if (Yii::$app->user->can('logist') && $model->state_id == PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ): ?>

        <?= $buttonToDraft ?>

        <?= $form->field($model, 'comment')->hiddenInput()->label(false) ?>

        <?php endif; ?>
        <?php if (Yii::$app->user->can('root') && $model->state_id == PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ): ?>
        <?= $buttonToDraft ?>

        <?= Html::submitButton('Согласовать', ['class' => 'btn btn-success btn-lg', 'name' => 'order_approve', 'title' => 'Согласовать и сразу отправить на оплату']) ?>

        <?= Html::submitButton('<i class="fa fa-times" aria-hidden="true"></i> Отказать', ['class' => 'btn btn-danger btn-lg', 'name' => 'order_reject', 'title' => 'Отказать в согласовании (обязательно нужно будет указать причину согласования)']) ?>
        <?php endif; ?>
        <?php if ($model->state_id == PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН): ?>
        <?php if (Yii::$app->user->can('root') || Yii::$app->user->can('accountant') || Yii::$app->user->can('accountant_b')): ?>
        <?= Html::submitButton('Оплачено', ['class' => 'btn btn-success btn-lg', 'name' => 'order_paid', 'title' => 'Установить признак "Оплачено"']) ?>

        <?php endif; ?>
        <?php if (Yii::$app->user->can('root')): ?>
        <?= Html::submitButton('<i class="fa fa-times" aria-hidden="true"></i> Отозвать', ['class' => 'btn btn-danger btn-lg', 'name' => 'order_reject', 'title' => 'Отменить согласование']) ?>
        <?php endif; ?>
        <?php endif; ?>

        <?php if ($model->state_id == PaymentOrdersStates::PAYMENT_STATE_ОТКАЗ && (Yii::$app->user->can('root') || Yii::$app->user->can('logist'))): ?>
        <?= Html::submitButton('<i class="fa fa-refresh" aria-hidden="true"></i> Подать повторно', ['class' => 'btn btn-default btn-lg', 'name' => 'order_repeat', 'title' => 'Подать отклоненный ордер на согласование повторно']) ?>
        <?php endif; ?>

        <?php if ($model->state_id == PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН): ?>
        <?= Html::submitButton('<i class="fa fa-save" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-lg', 'name' => 'cc_provided', 'title' => 'Кнопка используется только для загрузки Акта выполненных работ к уже оплаченному ордеру']) ?>

        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
