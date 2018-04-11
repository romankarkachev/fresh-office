<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user \common\models\User */

$link = Yii::$app->urlManager->createAbsoluteUrl(['/login']);
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            Вы зарегистрировались в системе <strong><?= Yii::$app->name ?>.</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Для авторизации воспользуйтесь следующими данными:
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Логин (ваш ИНН): <strong><?= str_replace('ferryman', '', $user->username) ?></strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Пароль: <?= $user->password ?>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <?= Html::a('Авторизоваться', $link, ['class' => 'btn-primary']) ?>

        </td>
    </tr>
</table>
