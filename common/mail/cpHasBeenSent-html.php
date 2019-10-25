<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \common\models\CorrespondencePackages */
// если раскомментировать if (empty($model->canUnsubscribe)), то можно будет проверять, не отписан ли уже E-mail от рассылки

$track_num = trim($model->track_num);
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый клиент!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Вам отправлено заказное письмо
            <?= !empty($model->addressValue) ? ' по адресу: ' . trim($model->addressValue) : (!empty($model->address) ? ' по адресу ' . trim($model->address->src_address) : '') ?><?= $model->contact_person ? ', контактное лицо с вашей стороны &mdash; ' . $model->contact_person : '' ?>, трек-номер отправления: <?= $track_num ?>.
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <?= Html::a('Отслеживать', 'https://www.pochta.ru/tracking#' . $track_num, ['class' => 'btn-primary']) ?>

        </td>
    </tr>
    <tr>
        <td class="content-block">
            Если Вы не желаете получать более такие уведомления, Вы можете <?= Html::a('отписаться', Yii::$app->urlManager->createAbsoluteUrl(['/services/unsubscribe-from-cp-notifications', 'email' => $model->contact_email, 'ca' => $model->fo_id_company])) ?>.
        </td>
    </tr>
</table>
