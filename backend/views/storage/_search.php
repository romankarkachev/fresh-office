<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use common\models\TransportRequests;
use common\models\UploadingFilesMeanings;

/* @var $this yii\web\View */
/* @var $model common\models\FileStorageSearch */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="file-storage-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/storage'],
        'method' => 'get',
        'options' => ['id' => 'frmSearch'],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'ca_id')->widget(Select2::className(), [
                        'initValueText' => TransportRequests::getCustomerName($model->ca_id),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'language' => 'ru',
                        'options' => ['placeholder' => 'Введите наименование'],
                        'pluginOptions' => [
                            'minimumInputLength' => 3,
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
                        'pluginEvents' => [
                            'select2:select' => new JsExpression('function() { $("#frmSearch").submit() }'),
                        ],
                    ]) ?>

                </div>
                <?php if (!Yii::$app->user->can('logist')): ?>
                <div class="col-md-2">
                    <?= $form->field($model, 'type_id')->widget(Select2::className(), [
                        'data' => UploadingFilesMeanings::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <?php endif; ?>
                <div class="col-md-2">
                    <?= $form->field($model, 'ofn')->textInput(['placeholder' => 'Введите имя файла'])->label('Имя файла') ?>

                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info']) ?>

                <?= Html::a('Отключить отбор', ['/storage'], ['class' => 'btn btn-default']) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
