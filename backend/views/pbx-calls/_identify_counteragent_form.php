<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use \yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model backend\models\pbxIdentifyCounteragentForm */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="identify-counteragent-form">
    <?php $form = ActiveForm::begin([
        'id' => 'frmIdentifyCounteragent',
        'action' => ['/pbx-calls/apply-identification'],
        'enableAjaxValidation' => true,
        'validationUrl' => ['/pbx-calls/validate-identification'],
    ]); ?>

    <p>
        <strong>Внимание</strong>! Вы можете добавить данный номер телефона только к уже существующему контрагенту!
    </p>
    <div class="form-group">
        <label class="control-label">Номер телефона</label>
        <p class="form-control"><?= $model->phone ?></p>
    </div>
    <?php if (!empty($model->ambiguous)): ?>
    <div class="form-group">
        <label class="control-label">Номер телефона встречается у контрагентов:</label>
        <p class="form-control"><?= $model->ambiguous ?></p>
    </div>
    <?php endif; ?>
    <?= $form->field($model, 'fo_ca_id')->widget(Select2::className(), [
        'initValueText' => \common\models\TransportRequests::getCustomerName($model->fo_ca_id),
        'theme' => Select2::THEME_BOOTSTRAP,
        'language' => 'ru',
        'options' => ['placeholder' => 'Введите наименование'],
        'pluginOptions' => [
            'minimumInputLength' => 1,
            'language' => 'ru',
            'ajax' => [
                'url' => \yii\helpers\Url::to(['projects/direct-sql-counteragents-list']),
                'delay' => 500,
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term}; }')
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(result) { return result.text; }'),
            'templateSelection' => new JsExpression('function (result) { return result.text; }'),
        ],
    ]) ?>

    <?= $form->field($model, 'is_process_other_calls')->checkbox()->label(null, ['style' => 'padding-left: 0px;', 'title' => 'Установите признак, если хотите чтобы все звонки, где встречается данный номер телефона, были идентифицированы выбранным Вами контрагентом']) ?>

    <?= $form->field($model, 'call_id')->hiddenInput()->label(false) ?>

    <?= Html::button('<i class="fa fa-plus-circle" aria-hidden="true"></i> ' . \backend\models\pbxIdentifyCounteragentForm::BUTTON_SUBMIT_IDENTIFICATION_LABEL, ['id' => 'btnApplyIdentification', 'class' => 'btn btn-success btn-block']) ?>

    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
$("input").iCheck({checkboxClass: "icheckbox_square-green"});
JS
, \yii\web\View::POS_READY);
?>
