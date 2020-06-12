<?php

use backend\components\grid\GridView;
use backend\controllers\PoController;
use backend\controllers\AdvanceReportsController;
use kartik\datecontrol\DateControl;
use kartik\file\FileInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use common\models\Po;
use common\models\AuthItem;
use common\models\PaymentOrdersStates;

/* @var $this yii\web\View */
/* @var $model common\models\Po */
/* @var $ecoProject common\models\EcoProjects */
/* @var $dpLogs \yii\data\ActiveDataProvider */
/* @var $dpFiles \yii\data\ActiveDataProvider файлы, приаттаченные к платежному ордеру */
/* @var $dpProperties \yii\data\ActiveDataProvider свойства, которыми описывается статья, выбранная в платежном ордере */

$this->title = $model->modelRep . HtmlPurifier::process(' &mdash; Платежные ордеры | ') . Yii::$app->name;
if (in_array($model->state_id, PaymentOrdersStates::PAYMENT_STATES_SET_АВАНСОВЫЕ_ОТЧЕТЫ)) {
    $this->params['breadcrumbs'][] = AdvanceReportsController::ROOT_BREADCRUMB;
}
else {
    $this->params['breadcrumbs'][] = PoController::ROOT_BREADCRUMB;
}
$this->params['breadcrumbs'][] = $model->modelRep;

$formName = $model->formName();
$formNameId = strtolower($model->formName());

$formId = Po::DOM_IDS['FORM_PO_ID'];
$btnRejectId = Po::DOM_IDS['BUTTON_REJECT_ID'];
$btnSubmitRejectId = Po::DOM_IDS['BUTTON_SUBMIT_REJECT_ID'];
?>
<div class="payment-orders-update">
    <?php $form = ActiveForm::begin(['id' => $formId]); ?>

    <div class="form-group">
        <p class="lead">
        <?php if (!in_array($model->state_id, PaymentOrdersStates::PAYMENT_STATES_SET_АВАНСОВЫЕ_ОТЧЕТЫ)): ?>
            Пользователь
            <?= $model->createdByProfileName ?>
            запросил оплату контрагенту
            <strong><?= $model->companyName?></strong>. Текущий статус: <strong><?= $model->stateName ?></strong>. Сумма: <strong><?= Yii::$app->formatter->asDecimal($model->amount, 2) ?></strong>.
        <?php else: ?>
            <?= $model->createdByProfileName ?> отчитывается за сумму в <strong><?= \common\models\FinanceTransactions::getPrettyAmount($model->amount, 'html') ?></strong> по оплате контрагенту <strong><?= $model->companyName?></strong>.
        <?php endif; ?>
        </p>
    </div>
    <?php if (!in_array($model->state_id, [
        PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК,
        PaymentOrdersStates::PAYMENT_STATE_АВАНСОВЫЙ_ОТЧЕТ,
        PaymentOrdersStates::PAYMENT_STATE_ОТКЛОНЕННЫЙ_АВАНСОВЫЙ_ОТЧЕТ,
    ])): ?>
    <p>
        Статья расходов: <?= $model->eiRep ?>

    </p>
    <?php if (!empty($model->ep)): ?>
    <p>
        Проект по экологии: <?= $ecoProject->representation ?>

    </p>
    <?php endif; ?>
    <?php endif; ?>
    <?php if ($dpProperties->getTotalCount() > 0): ?>
    <div class="row">
        <div class="col-md-4">
            <?= GridView::widget([
                'dataProvider' => $dpProperties,
                'tableOptions' => ['class' => 'table table-striped table-responsive'],
                'layout' => '{items}',
                'columns' => [
                    'propertyName',
                    'valueName',
                ],
            ]); ?>

        </div>
    </div>
    <?php endif; ?>
    <?php if ((Yii::$app->user->can('root') || Yii::$app->user->can('accountant_b')) && (in_array($model->state_id, PaymentOrdersStates::PAYMENT_STATES_SET_RECORD_CONFIRMED))): ?>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'paid_at')->widget(DateControl::class, [
                'value' => $model->paid_at,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:U',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => ['placeholder' => 'Выберите дату оплаты'],
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ]) ?>

        </div>
    </div>
    <?php endif; ?>

    <?= $form->field($model, 'files', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false); ?>

    <?php if ((Yii::$app->user->can(AuthItem::ROLE_ROOT) || Yii::$app->user->can(AuthItem::ROLE_ACCOUNTANT_B) || Yii::$app->user->can(AuthItem::ROLE_ACCOUNTANT_SALARY)) && (in_array($model->state_id, PaymentOrdersStates::PAYMENT_STATES_SET_RECORD_CONFIRMED))): ?>
    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите комментарий, например, причину отказа']) ?>

    <?php endif; ?>
    <div class="form-group">
        <?= $model->renderSubmitButtons() ?>

    </div>
    <?php ActiveForm::end(); ?>

    <?= $this->render('_logs', ['dataProvider' => $dpLogs]); ?>

    <?= $this->render('_files', ['dataProvider' => $dpFiles]); ?>

    <?php if ((Yii::$app->user->can('root') || Yii::$app->user->can('accountant_b')) && ($model->state_id == PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН) || in_array($model->state_id, PaymentOrdersStates::PAYMENT_STATES_SET_АВАНСОВЫЕ_ОТЧЕТЫ)): ?>
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
<div id="modalWindow" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header"><h4 id="modalTitle" class="modal-title">Введите причину отказа</h4></div>
            <div id="modalBody" class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" id="<?= $btnSubmitRejectId ?>" class="btn btn-success" data-dismiss="modal">Продолжить</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<?php
$urlRenderFieldReason = Url::to(PoController::URL_RENDER_FIELD_REASON_AS_ARRAY);

$this->registerJs(<<<JS

// Обработчик щелчка по кнопке "Отказ".
//
function btnRejectOnClick(e) {
    e.preventDefault();
    $("#modalBody").load("$urlRenderFieldReason");
    $("#modalWindow").modal();

    return false;
} // btnRejectOnClick()

// Обработчик нажатия на кнопку "Продолжить" в модальном окне.
//
function btnSubmitReject() {
    var rejectReason = $("#$formNameId-reject_reason").val();
    \$fieldComment = $("#$formNameId-comment");
    var comment = \$fieldComment.val();
    if (rejectReason) {
        if (comment.trim() != "") {
            comment = comment.trim() + "\\r\\n";
        }
        comment += "Причина отказа: " + rejectReason;
        \$fieldComment.val(comment);
    }

    $("#$formId").submit();
    return false;
} // btnSubmitReject()

$(document).on("click", "#$btnRejectId", btnRejectOnClick);
$(document).on("click", "#$btnSubmitRejectId", btnSubmitReject);
JS
, \yii\web\View::POS_READY);
?>
