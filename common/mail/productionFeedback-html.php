<?php
/* @var $this yii\web\View */
/* @var $body string текст письма */
/* @var $senderName string имя отправителя письма (старшего смены обычно) */
/* @var $mismatches array табличная часть с несоответствиями */
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый менеджер!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <?= $body ?>

    </tr>
    <?php if (is_array($mismatches) && count($mismatches) > 0): ?>
    <tr>
        <td class="content-block">
            <table class="invoice">
                <tr><td><p class="alert alert-bad">Обратите внимание на расхождения</p></td></tr>
                <tr>
                    <td>
                        <table class="invoice-items" cellpadding="0" cellspacing="0">
                            <?php
                            foreach ($mismatches as $row):
                            ?>
                            <tr>
                                <td><?= $row['name'] ?></td>
                                <td>&nbsp;</td>
                                <td class="aligncenter" width="90px"><?= $row['value'] ?></td>
                                <td>&nbsp;</td>
                                <td class="aligncenter" width="90px"><?= $row['fact'] ?></td>
                            </tr>
                            <?php
                            endforeach;
                            ?>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <?php endif; ?>
    <tr>
        <td class="content-block">
            <p><strong>Составитель письма</strong>: <?= $senderName ?></p>
        </td>
    </tr>
</table>
