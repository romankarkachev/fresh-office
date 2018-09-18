<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $token string */

$link = 'http://' . Yii::$app->params['serverIp'] . '/customer/register?token=' . $token;
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый клиент!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Вас приветсвует компания по обращению с отходами. Приглашаем Вас зарегистрироваться в
            личном кабинете. Вы сможете получить доступ к своим заказам, оформлять их в электронном виде, поставить
            оценку за прошлые, а также отправить жалобу и предложение.
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Обращаем внимание, что данный кабинет работает в тестовом режиме, мы будем рады обратной связи.
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Телефон технической поддержки: <strong>8 495 926 4766</strong> или Вы всегда можете обратиться к своему
            менеджеру.
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <?= Html::a('Зарегистрироваться', $link, ['class' => 'btn-success']) ?>

        </td>
    </tr>
</table>
