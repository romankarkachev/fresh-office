<?php

/* @var $this yii\web\View */
/* @var $headliner \common\models\foCompany главная карточка */
/* @var $customersAffected array результаты выполнения, содержит идентификаторы и наименования обработанных контрагентов */
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый менеджер!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Прошу обратить внимание на качество ведения учета Вами. К сожалению, сегодня оно не соответствует курсу
            нашей компании, не отвечает современным высоким стандартам. Было выполнено объединение карточек одинаковых
            контрагентов в одну: <strong><?= $headliner->COMPANY_NAME ?></strong> (ID <?= $headliner->ID_COMPANY ?>).
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <ul>
            <?php foreach ($customersAffected as $customer): ?>
            <?php if (!empty($customer['active'])) continue; ?>
            <li><?= $customer['name'] ?> (ID <?= $customer['id'] ?>)</li>
            <?php endforeach; ?>
            </ul>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Вся информация из перечисленных контрагентов перенесена в основную карточку, а сами они помещены в корзину.
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Убедительная просьба вести учет на высоком уровне, быть предельно внимательным и ответственным, не дублировать карточки контрагентов.
        </td>
    </tr>
</table>
