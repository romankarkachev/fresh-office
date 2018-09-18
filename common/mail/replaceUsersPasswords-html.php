<?php

/* @var $this yii\web\View */
/* @var $iterator integer */
/* @var $usersAffected array результаты выполнения, содержит идентификаторы пользователей и их новые пароли */
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый администратор!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <?= Yii::$app->formatter->asDate(time(), 'php:d F Y в H:i') ?> несколько пользователей получили новые пароли.
            В данном письме содержатся все изменения, которые были применены.
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <table class="invoice" width="100%" cellpadding="0" cellspacing="0">
            <tr class="invoice-items">
                <th>#</th>
                <th>ID</th>
                <th>FO</th>
                <th>Имя</th>
                <th>Логин</th>
                <th>Новый пароль</th>
            </tr>
            <?php foreach ($usersAffected as $user): ?>
                <tr class="invoice-items">
                    <td><?= $iterator ?></td>
                    <td><?= $user['id'] ?></td>
                    <td><?= $user['fo_id'] ?></td>
                    <td><?= $user['name'] ?></td>
                    <td><?= $user['login'] ?></td>
                    <td><?= $user['password'] ?></td>
                </tr>
            <?php $iterator++; ?>
            <?php endforeach; ?>
            </table>
        </td>
    </tr>
</table>
