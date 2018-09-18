<?php
/* @var $date string дата, за которую собраны проекты */
/* @var $projectsIds string дата, за которую собраны проекты */
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый отдел логистики!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Следующие проекты неактуальны <?= $date ?>: <?= $projectsIds ?>.
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Пожалуйста, обновите статусы в CRM и не забывайте следить за их своевременным изменением.
        </td>
    </tr>
</table>
