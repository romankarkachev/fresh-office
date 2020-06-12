<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;
use yii\web\JsExpression;
use common\models\TransportRequests;
use common\models\User;
use backend\controllers\TendersController;

/* @var $this yii\web\View */
/* @var $model common\models\TendersSearch */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="tenders-search">
    <?php $form = ActiveForm::begin([
        'action' => TendersController::ROOT_URL_AS_ARRAY,
        'method' => 'get',
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'fo_ca_id')->widget(Select2::class, [
                        'initValueText' => TransportRequests::getCustomerName($model->fo_ca_id),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'language' => 'ru',
                        'options' => ['placeholder' => 'Введите наименование'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 1,
                            'language' => 'ru',
                            'ajax' => [
                                'url' => Url::to(['projects/direct-sql-counteragents-list']),
                                'delay' => 500,
                                'dataType' => 'json',
                                'data' => new JsExpression('function(params) { return {q:params.term}; }')
                            ],
                            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                            'templateResult' => new JsExpression('function(result) { return result.text; }'),
                            'templateSelection' => new JsExpression('function (result) { return result.text; }'),
                        ],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'manager_id')->widget(Select2::class, [
                        'data' => User::arrayMapForSelect2(User::ARRAY_MAP_OF_USERS_BY_ALL_ROLES),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'responsible_id')->widget(Select2::class, [
                        'data' => User::arrayMapForSelect2(User::ARRAY_MAP_OF_USERS_BY_ALL_ROLES),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'state_id')->widget(Select2::class, [
                        'data' => \common\models\TendersStates::arrayMapForSelect2(true),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ])->label('Статус') ?>

                </div>
            </div>
            <?= $form->field($model, 'title')->textInput(['placeholder' => 'Введите часть наименования']) ?>

            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info', 'id' => 'btnSearch']) ?>

                <?= Html::a('Отключить отбор', TendersController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
