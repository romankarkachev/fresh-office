<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use common\models\TransportRequests;
use backend\models\CustomerInvitationForm;

/* @var $this yii\web\View */
/* @var $model backend\models\CustomerInvitationForm */
/* @var $form yii\bootstrap\ActiveForm */

$this->title = 'Пригласить клиента зарегистрироваться в личном кабинете | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Отправка приглашения клиенту';

$emailTemplate = '<div class="input-group">
    <span class="input-group-addon">@</span>
    {input}
    <span id="spanButtonSubmit" class="input-group-btn">
        <button id="btnSendInvitation" class="btn btn-default" type="submit">Отправить</button>
    </span>
</div>{error}';

$searchUseGateway = strtolower($model->formName() . '-is_use_gateway');
?>

<div class="invite-customer-form">
    <?php $form = ActiveForm::begin([
        'id' => 'frmInviteCustomer',
        'enableAjaxValidation' => true,
        'validationUrl' => ['validate-customer-invitation-form'],
    ]); ?>

    <p>Через эту форму можно отправить приглашение представителю заказчика создать личный кабинет для работы в нашей системе.</p>
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'fo_id_company')->widget(Select2::className(), [
                'initValueText' => TransportRequests::getCustomerName($model->fo_id_company),
                'theme' => Select2::THEME_BOOTSTRAP,
                'language' => 'ru',
                'options' => ['placeholder' => 'Введите наименование'],
                'pluginOptions' => [
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
                'pluginEvents' => [
                    'change' => new JsExpression('function() { customerOnChange($(this).val()); }'),
                ],
            ]) ?>

        </div>
        <div id="block-email" class="col-md-2">
            <?= $this->render('_invite_fields', ['model' => $model, 'emails' => CustomerInvitationForm::arrayMapOfEmailsForSelect2($model->fo_id_company), 'form' => $form]) ?>

        </div>
        <div class="col-md-3">
            <label for="<?= $searchUseGateway ?>" class="control-label"><?= $model->getAttributeLabel('is_use_gateway') ?></label>
            <div class="form-group">
                <div class="checkbox" style="margin-top:5px;">
                    <?= Html::input('checkbox', $model->formName() . '[is_use_gateway]', 1, ['id' => $searchUseGateway, 'checked' => !empty($model->is_use_gateway)]) ?>

                </div>
            </div>
        </div>
    </div>
    <p>
        Если будет установлен признак использования шлюза, то письмо будет отправлено на ящик <?= Html::mailto('vip@st77.ru') ?>,
        менеджер должен будет перенаправить клиенту. Регистрироваться клиент будет на тот ящик, который Вы выберете здесь.
    </p>
    <?= Html::submitButton('<i class="fa fa-paper-plane" aria-hidden="true"></i> Отправить', ['class' => 'btn btn-success btn-lg']) ?>

    <?php ActiveForm::end(); ?>

</div>
<?php
$urlComposeFields = Url::to(['/invite-customer/compose-fields']);

$this->registerJs(<<<JS

$("input").iCheck({checkboxClass: "icheckbox_square-green"});

// Обработчик изменения значения в поле "Заказчик".
//
function customerOnChange(ca_id) {
    if (ca_id != 0 && ca_id != "" && ca_id != undefined) {
        \$block = $("#block-email");
        \$block.html("<p class=\"text-center\"><i class=\"fa fa-cog fa-spin fa-2x text-muted\"></i><span class=\"sr-only\">Подождите...</span></p>");
        \$block.load("$urlComposeFields?ca_id=" + ca_id);
    }
} // customerOnChange()
JS
, \yii\web\View::POS_READY);
?>
