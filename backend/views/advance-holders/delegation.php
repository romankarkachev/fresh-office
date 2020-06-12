<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\FinanceTransactions */
/* @var $form yii\bootstrap\ActiveForm */

$this->title = 'Делегирование финансовых обязательств другому подотчетному лицу | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\AdvanceHoldersController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Передача денежных средств';
?>

<div class="finance-advance-holders-delegation">
    <?php if (empty($model->amount)): ?>
    <p class="text-muted">В настоящий момент у Вас отсутствуют финансовые обязательства, поэтому передача денежных средств невозможна.</p>
    <?php else: ?>
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'receiver_id')->widget(Select2::class, [
                'data' => User::arrayMapForSelect2(User::USERS_ALL_JOIN_FINANCE_BALANCE),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'pluginOptions' => [
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function (data) { return data.text; }'),
                ],
            ])->label('Выберите получателя') ?>

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
    </div>

    <?= $form->field($model, 'sender_id', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-money" aria-hidden="true"></i> Провести', ['class' => 'btn btn-success btn-lg']) ?>

    </div>

    <?php ActiveForm::end(); ?>

    <?php endif; ?>
</div>
