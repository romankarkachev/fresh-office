<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $token string */

$link = 'http://' . Yii::$app->params['serverIp'] . '/ferryman/register?token=' . $token;
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый перевозчик!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Приглашаем вас зарегистрироваться в личном кабинете нашей компании. Вы получите доступ к своим рейсам,
            водителям, транспорту, банковским реквизитам.
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <?= Html::a('Зарегистрироваться', $link, ['class' => 'btn-success']) ?>

        </td>
    </tr>
</table>
