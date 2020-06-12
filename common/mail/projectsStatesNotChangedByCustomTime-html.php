<?php

use common\models\foProjects;

/* @var $items array массив проектов со значительно просроченными статусами */
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый отдел логистики!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Обратите внимание на проекты, у которых статусы не изменялись критическое время.
        </td>
    </tr>
    <?php foreach ($items as $item): ?>
    <tr>
        <td class="content-block">
            <strong><?= $item['stateName'] ?></strong> (<?= trim(foProjects::downcounter($item['time'])) ?>)
            <p>Проекты: <?= implode(', ', $item['projects']) ?></p>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
