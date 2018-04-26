<?php

use yii\helpers\Html;
use yii\helpers\Url;
use customer\models\CustomerRequestForm;

/* @var $this yii\web\View */
/* @var $caData array массив со всевозможными данными по контрагенту */

$this->title = 'Добро пожаловать! | '.Yii::$app->name;

// номер договора с контрагентом
$contractNumRep = '<span class="text-muted">номер отсутствует</span>';
if (!empty($caData['contractNum'])) $contractNumRep = '№ ' . $caData['contractNum'];

// срок действия договора с контрагентом
$contractExpiredAt = '';
if (!empty($caData['contractExpiredAt'])) {
    $warning = '';
    if (strtotime($caData['contractExpiredAt']) <= time()) $warning = ' <i class="fa fa-exclamation-triangle text-danger" aria-hidden="true" title="Срок действия договора истек!"></i>';
    $contractExpiredAt = ' действителен до ' . Yii::$app->formatter->asDate($caData['contractExpiredAt'], 'php:d F Y г.') . ' г.' . $warning;
}

// персональный менеджер контрагента
$managerNameRep = '';
$managerContactsRep = '';
if (!empty($caData['responsibleManagerName'])) $managerNameRep = $caData['responsibleManagerName'];
if (!empty($caData['responsibleManagerPhone'])) $managerNameRep .= (empty($managerNameRep) ? 'Телефон менеджера: ' : ', тел.: ') . $caData['responsibleManagerPhone'] . '.';
if (!empty($caData['responsibleManagerEmail'])) $managerContactsRep = 'E-mail менеджера: ' . Html::mailto($caData['responsibleManagerEmail'], $caData['responsibleManagerEmail']);

// количество баллов, рассчитанное от оборота с клиентом
$balanceRep = 'нет баллов';
if (!empty($caData['balance'])) $balanceRep = \common\models\foProjects::declension(Yii::$app->formatter->asInteger($caData['balance']), ['балл','балла','баллов']);
// <i class="fa fa-rub"></i>

// контакты с контрагентом
$contactsRep = '';
?>
<div class="welcome-dashboard" style="padding-top: 1.5rem;">
    <div class="row">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-accent-primary">
                <div class="card-block">
                    <div class="h4 m-0">Ваш договор</div>
                    <div><?= $contractNumRep ?></div>
                    <small class="text-muted"><?= $contractExpiredAt ?></small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-accent-primary">
                <div class="card-block">
                    <div class="h4 m-0">Ваш менеджер</div>
                    <div><?= $managerNameRep ?></div>
                    <small class="text-muted"><?= $managerContactsRep ?></small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-accent-primary">
                <div class="card-block">
                    <div class="h4 m-0">Ваш баланс</div>
                    <div><?= $balanceRep ?></div>
                    <small class="text-muted">рассчитывается от суммы оплаченных заказов</small>
                </div>
            </div>
        </div>
    </div>
    <div class="jumbotron">
        <h1>Добро пожаловать!</h1>

        <p class="lead">Вы находитесь в личном кабинете.</p>

        <p class="mb-0"><?= Html::a('Мои заказы', ['/orders'], ['class' => 'btn btn-lg btn-success']) ?></p>
    </div>
    <div class="row">
        <?php if (!empty($caData['contacts'])): ?>
            <div class="col-md-<?= !empty($caData['ratingProjects']) ? 6: 12 ?>">
                <div class="card">
                    <div class="card-header card-header-info card-header-inverse">Ближайшие контакты с вашей организацией</div>
                    <div class="card-block">
                        <table class="table table-responsive-sm table-hover table-outline mb-0">
                            <thead class="thead-light">
                            <tr>
                                <th>Контактное лицо и дата</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($caData['contacts'] as $contact): ?>
                                <tr>
                                    <td>
                                        <div><?= Yii::$app->formatter->asDate($contact['date'], 'php:d F Y г.') ?></div>
                                        <div class="small text-muted">
                                            <?= $contact['person'] ?>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!empty($caData['ratingProjects'])): ?>
            <div class="col-md-<?= !empty($caData['contacts']) ? 6: 12 ?>">
                <?= $this->render('_projects_ratings', ['ratingProjects' => $caData['ratingProjects']]) ?>

            </div>
        <?php endif; ?>
    </div>
    <div class="card">
        <div class="card-header">
            <i class="fa fa-align-justify"></i> Обратиться в нашу компанию
            <small>выберите любую из возможностей</small>
        </div>
        <div>
            <div class="list-group">
                <a href="#" id="btnRequestForm<?= CustomerRequestForm::ТИП_ОБРАЩЕНИЯ_ВЫЗОВ_МЕНЕДЖЕРА ?>" data-id="<?= CustomerRequestForm::ТИП_ОБРАЩЕНИЯ_ВЫЗОВ_МЕНЕДЖЕРА ?>" class="list-group-item list-group-item-action flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1 text-primary">Приезжайте ко мне</h5>
                        <small class="text-muted">Быстрая реакция</small>
                    </div>
                    <p class="mb-1">Вы можете пригласить нашего представителя к себе в офис для обсуждения деталей сотрудничества.</p>
                    <small class="text-muted">Предложить детали встречи можно в открывающемся окне.</small>
                </a>
                <a href="#" id="btnRequestForm<?= CustomerRequestForm::ТИП_ОБРАЩЕНИЯ_ОБРАТНАЯ_СВЯЗЬ ?>" data-id="<?= CustomerRequestForm::ТИП_ОБРАЩЕНИЯ_ОБРАТНАЯ_СВЯЗЬ ?>" class="list-group-item list-group-item-action flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1 text-primary">Связаться со мной</h5>
                        <small class="text-muted">Быстрая реакция</small>
                    </div>
                    <p class="mb-1">Воспользуйтесь данной функцией, если желаете, чтобы ответственный менеджер связался с Вами.</p>
                    <small class="text-muted">Есть возможность ввести комментарий в открывшемся окне.</small>
                </a>
                <?php if (!empty($caData['canCreateProject'])): ?>
                <a href="#" id="btnRequestForm<?= CustomerRequestForm::ТИП_ОБРАЩЕНИЯ_ЗАКАЗ ?>" data-id="<?= CustomerRequestForm::ТИП_ОБРАЩЕНИЯ_ЗАКАЗ ?>" class="list-group-item list-group-item-action flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1 text-primary">Нужен вывоз отходов</h5>
                        <small class="text-muted">Быстрая реакция</small>
                    </div>
                    <p class="mb-1">Если Вы уже готовы к сотрудничеству, нажмите здесь, чтобы создать и запустить в работу заказ.</p>
                    <small class="text-muted">Вы можете дополнить заказ произвольным сообщением в открывшемся окне.</small>
                </a>
                <?php endif; ?>
                <a href="#" id="btnRequestForm<?= CustomerRequestForm::ТИП_ОБРАЩЕНИЯ_ЖАЛОБА ?>" data-id="<?= CustomerRequestForm::ТИП_ОБРАЩЕНИЯ_ЖАЛОБА ?>" class="list-group-item list-group-item-action flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1 text-primary">Пожаловаться</h5>
                        <small class="text-muted">Быстрая реакция</small>
                    </div>
                    <p class="mb-1">Возможны неприятные ситуации, которые мы постараемся сразу же разрешить. Нажмите здесь, чтобы обратиться напрямую к руководству нашей компании.</p>
                    <small class="text-muted">Вам необходимо будет написать в свободной форме свои замечания и предложения в открывшемся окне.</small>
                </a>
            </div>
        </div>
    </div>
</div>
<div id="mwRequestForm" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="modal_title" class="modal-title">Отправка запроса</h4>
                <small id="modal_title_right" class="form-text"></small>
            </div>
            <div id="modal_body" class="modal-body"></div>
            <div class="modal-footer">
                <?= Html::button('<i class="fa fa-paper-plane"></i> Отправить', ['class' => 'btn btn-success', 'id' => 'btnSend']) ?>

                <?= Html::button('Закрыть', ['class' => 'btn btn-secondary', 'data-dismiss' => 'modal']) ?>

            </div>
        </div>
    </div>
</div>
<?php
$urlRequestForm = Url::to(['/default/request-form']);

$this->registerJs(<<<JS
// Обработчик щелчка по кнопке "Отправить".
// Выполняет отправку обращения.
//
function sendRequestOnClick() {
    $("#frmSendRequest").submit();

    return false;
} // sendRequestOnClick()

// Обработчик щелчка на кнопкам "Создать заявку для перевозчика" в списке проектов.
// Отображает форму создания заявки по шаблону.
//
function requestFormOnClick() {
    type = $(this).attr("data-id");
    if (type != "" && type != undefined) {
        $("#modal_body").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#mwRequestForm").modal();
        $("#modal_body").load("$urlRequestForm?type=" + type);
    }

    return false;
} // requestFormOnClick()

$(document).on("click", "a[id ^= 'btnRequestForm']", requestFormOnClick);
$(document).on("click", "#btnSend", sendRequestOnClick);
JS
, \yii\web\View::POS_READY);
?>
