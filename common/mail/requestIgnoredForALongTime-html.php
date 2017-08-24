<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $requestId integer */
/* @var $user_name string */

$link = Yii::$app->urlManager->createAbsoluteUrl(['/transport-requests/update', 'id' => $requestId]);
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Запрос игнорируется</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Обратите внимание на запрос транспорта, который игнорируется уже продолжительное время. Автор обращения: <?= $user_name ?>.
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <?= Html::a('Открыть запрос', $link, ['class' => 'btn-primary']) ?>

        </td>
    </tr>
</table>
