<?php

use backend\components\grid\GridView;
use backend\controllers\PoController;
use common\models\PaymentOrdersStates;
use kartik\datecontrol\DateControl;
use kartik\file\FileInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\Po */
/* @var $dpFiles \yii\data\ActiveDataProvider файлы, приаттаченные к платежному ордеру */
/* @var $dpProperties \yii\data\ActiveDataProvider свойства, которыми описывается статья, выбранная в платежном ордере */

$this->title = $model->modelRep . HtmlPurifier::process(' &mdash; Платежные ордеры | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = PoController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->modelRep;
?>
<div class="payment-orders-update">
    <?php $form = ActiveForm::begin(); ?>

    <div class="form-group">
        <p class="lead">
            Пользователь
            <?= $model->createdByProfileName ?>
            запросил оплату контрагенту
            <strong><?= $model->companyName?></strong>. Текущий статус: <strong><?= $model->stateName ?></strong>. Сумма: <strong><?= Yii::$app->formatter->asDecimal($model->amount, 2) ?></strong>.
        </p>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?= GridView::widget([
                'dataProvider' => $dpProperties,
                'layout' => '{items}',
                'columns' => [
                    'propertyName',
                    'valueName',
                ],
            ]); ?>

        </div>
    </div>
    <?php if ((Yii::$app->user->can('root') || Yii::$app->user->can('accountant_b')) && (in_array($model->state_id, PaymentOrdersStates::PAYMENT_STATES_SET_RECORD_CONFIRMED))): ?>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'paid_at')->widget(DateControl::className(), [
                'value' => $model->paid_at,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:U',
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

    <?= $form->field($model, 'comment', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false); ?>

    <?php if ((Yii::$app->user->can('root') || Yii::$app->user->can('accountant_b')) && (in_array($model->state_id, PaymentOrdersStates::PAYMENT_STATES_SET_RECORD_CONFIRMED))): ?>
    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите причину отказа']) ?>

    <?php endif; ?>
    <div class="form-group">
        <?= $model->renderSubmitButtons() ?>

    </div>
    <?php ActiveForm::end(); ?>

    <?= $this->render('_files', ['dataProvider' => $dpFiles]); ?>

    <?php if ((Yii::$app->user->can('root') || Yii::$app->user->can('accountant_b')) && ($model->state_id == PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН)): ?>
    <?= FileInput::widget([
        'id' => 'new_files',
        'name' => 'files[]',
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'maxFileCount' => 10,
            'uploadAsync' => false,
            'uploadUrl' => Url::to(PoController::URL_UPLOAD_FILES_AS_ARRAY),
            'uploadExtraData' => [
                'obj_id' => $model->id,
            ],
        ]
    ]) ?>

    <?php endif; ?>
</div>
