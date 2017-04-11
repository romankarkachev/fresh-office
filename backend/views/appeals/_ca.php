<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use common\models\Appeals;
use common\models\DirectMSSQLQueries;
use common\models\ResponsibleFornewca;

/* @var $this yii\web\View */
/* @var $model common\models\Appeals */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $matches array */

$company_id = '-';
if ($model->fo_id_company != null && $model->fo_id_company != '') {
    $company_id = $model->fo_id_company;
    // набор ответственных лиц - только для новых контрагентов (ограниченный список)
    $responsibleDataSet = DirectMSSQLQueries::arrayMapOfManagersForSelect2();
}
else
    // набор ответственных лиц полный
    $responsibleDataSet = ResponsibleFornewca::arrayMapForSelect2();

$company_name = 'Контрагент не идентифицирован';
if ($model->fo_company_name != null && $model->fo_company_name != '') $company_name = $model->fo_company_name;

$company_state = $model->getCaStateName();
?>

<?= $form->field($model, 'fo_id_company')->hiddenInput()->label(false) ?>

<?= $form->field($model, 'fo_company_name')->hiddenInput()->label(false) ?>

<?= $form->field($model, 'ca_state_id')->hiddenInput()->label(false) ?>

<?php
if (isset($matches)) {
    echo '
<div id="table-multiple" class="form-group">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Наименование</th>
                <th>Совпадение</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($matches as $match) {
        echo '
            <tr>
                <th scope="row">' . $match['caId'] . '</th>
                <td>' . Html::a($match['caName'], '#', ['id' => 'select-row', 'data' => [
                    'caId' => $match['caId'],
                    'caName' => $match['caName'],
                    'stateId' => $match['stateId'],
                    'stateName' => $model->getCaStateName($match['stateId']),
                    'managerId' => $match['managerId'],
                ]]) . '</td>
                <td>' . $match['contact'] . '</td>
            </tr>
';
    }

    echo '
        </tbody>
    </table>
</div>';
}
?>
<div id="block-ca-hidden"<?= isset($matches) ? ' class="collapse"' : '' ?>>
    <div class="row">
        <div class="col-md-1">
            <div class="form-group">
                <label class="control-label" for="appeals-fo_id_company">ID</label>
                <p id="lbl-company-id"><?= $company_id ?></p>
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-group">
                <label class="control-label" for="appeals-fo_company_name">Наименование</label>
                <p id="lbl-company-name"><?= $company_name ?></p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label class="control-label" for="appeals-fo_company_state">Статус</label>
                <p id="lbl-state-name"><?= $company_state ?></p>
            </div>
        </div>
    </div>
    <div id="block-fo_id_manager_notif" class="form-group"></div>
    <div class="row">
        <div class="col-md-5">
            <?= $form->field($model, 'fo_id_manager')->widget(Select2::className(), [
                'data' => $responsibleDataSet,
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'disabled' => $model->state_id == Appeals::APPEAL_STATE_CLOSED,
                    'pluginEvents' => [
                    'select2:select' => new JsExpression('function() {
        ca_id = $("#appeals-fo_id_company").val();
        receiver_id = $(this).val();
        appeal_id = $("#btn-identify-ca").attr("data-model-id");
        ManagerOnChange(appeal_id, ca_id, receiver_id);
    }'),
                    'select2:selecting' => new JsExpression('function() {
        ca_id = $("#appeals-fo_id_company").val();
        if (ca_id == "")
            return confirm("Контрагент не идентифицирован, будет создан новый, а выбранному ответственному будет отправлена задача. Продолжить?");
        else
            return confirm("При изменении ответственного лица ему будет передан выбранный контрагент, а также назначена задача с текстом обращения из заявки. Продолжить?");
    }'),
                ]
            ]) ?>

        </div>
    </div>
</div>
<?php
$url_delegate_counteragent = Url::to(['/appeals/delegate-counteragent']);
$this->registerJs(<<<JS
// Функция возвращает html-код уведомления, сформированного из переданных параметров.
//
function drawAlert(type, icon, body) {
    return '<div id="block-fo_id_manager_alert" class="alert alert-' + type + '" role="alert">' +
'            <span class="glyphicon glyphicon-' + icon + '" aria-hidden="true"></span> ' + body +
'        </div>';
} // drawAlert()

// Функция выполняет передачу контрагента другому менеджеру,
// создание сообщения для пользователя в CRM о том, что ему был передан контрагент,
// а также создание задачи новому менеджеру с текстом обращения.
//
function delegateCounteragent(appeal_id, ca_id, receiver_id) {
    $("#block-fo_id_manager_notif").html('<p><i class="fa fa-cog fa-spin fa-2x text-muted"></i><span class="sr-only">Подождите...</span></p>');
    $.post("$url_delegate_counteragent", {appeal_id: appeal_id, ca_id: ca_id, receiver_id: receiver_id}, function(data) {
        operation_pending = "Передача";
        operation_done = "выполнена";
        if (ca_id == "") {
            operation_pending = "Создание";
            operation_done = "выполнено";
        }
        if ($.isArray(data)) {
            text_errors = "<strong>" + operation_pending + " контрагента не " + operation_done + "!</strong>";
            $.each(data, function(key, value) {
                text_errors += "<p>" + value + "</p>";
            });
            $("#block-fo_id_manager_notif").html(drawAlert("danger", "exclamation-sign", text_errors));
        }
        else if (data == true)
            $("#block-fo_id_manager_notif").html(drawAlert("success", "ok-circle", operation_pending + " контрагента " + operation_done + " успешно!"));
        else if (data == false)
            $("#block-fo_id_manager_notif").html(drawAlert("danger", "question-sign", "Обращение не обнаружено. " + operation_pending + " контрагента не " + operation_done + "!"));
    });
} // delegateCounteragent()

// Функция выполняет создание задачи для нового ответственного, а также передачу ему контрагента.
//
function ManagerOnChange() {
    delegateCounteragent(appeal_id, ca_id, receiver_id);
} // ManagerOnChange()
JS
, \yii\web\View::POS_READY);
?>
