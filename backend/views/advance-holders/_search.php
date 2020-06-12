<?php

use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use backend\controllers\AdvanceHoldersController;

/* @var $this yii\web\View */
/* @var $model common\models\FinanceAdvanceHoldersSearch */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="finance-advance-holders-search">
    <?php $form = ActiveForm::begin([
        'action' => AdvanceHoldersController::ROOT_URL_AS_ARRAY,
        'method' => 'get',
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'user_id')->widget(Select2::class, [
                        'data' => \common\models\FinanceAdvanceHolders::arrayMapOfHoldersForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('Подотчетник') ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchBalanceStart', [
                        'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
                    ])->widget(MaskedInput::className(), [
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
                        'title' => 'При заполнении только этого поля условие интерпретируется как "Сумма включительно и более"',
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'searchBalanceEnd', [
                        'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
                    ])->widget(MaskedInput::className(), [
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
                        'title' => 'При заполнении только этого поля условие интерпретируется как "Сумма включительно и менее"',
                    ]) ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', AdvanceHoldersController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
