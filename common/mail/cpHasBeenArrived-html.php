<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \common\models\CorrespondencePackages */

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
            Почтовое отправление с трек-номером
            <?= Html::a($track_num, 'https://www.pochta.ru/tracking#' . $track_num, ['title' => 'Открыть подробную информацию по этому отправлению на сайте почты России']) ?>
            прибыло в почтовое отделение. Пожалуйста, в скором времени получите его!
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Если Вы не желаете получать более такие уведомления, Вы можете <?= Html::a('отписаться', Yii::$app->urlManager->createAbsoluteUrl(['/services/unsubscribe-from-cp-notifications', 'email' => $model->contact_email, 'ca' => $model->fo_id_company])) ?>.
        </td>
    </tr>
</table>
