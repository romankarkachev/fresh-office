<?php

/* @var $this yii\web\View */
/* @var $customerName string наименование контрагента */
/* @var $userName string имя пользователя личного кабинета, представитель контрагента */
/* @var $compliantDetails string текст жалобы */
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Обратите внимание на жалобу клиента</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Пользователь личного кабинета <?= $userName ?> от лица <?= $customerName ?> отправил жалобу. Текст приводится ниже.
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <?= $compliantDetails ?>

        </td>
    </tr>
</table>
