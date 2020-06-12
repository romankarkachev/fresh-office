<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use common\models\FinanceTransactions;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\FinanceTransactions */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="finance-transactions-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'operation')->widget(Select2::className(), [
                'data' => FinanceTransactions::arrayMapOfOperationsTypesForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'user_id')->widget(Select2::className(), [
                'data' => User::arrayMapForSelect2(User::USERS_ALL_JOIN_FINANCE_BALANCE),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'pluginOptions' => [
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function (data) { return data.text; }'),
                ],
            ])->label('Подотчетник') ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'src_id')->widget(Select2::className(), [
                'data' => FinanceTransactions::arrayMapOfFundsSourcesForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

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

    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите произвольный комментарий']) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-money" aria-hidden="true"></i> Провести', ['class' => 'btn btn-success btn-lg']) ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
