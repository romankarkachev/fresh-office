<?php

use common\models\foProjects;

/* @var $items array массив пакетов со значительно просроченными статусами */
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            Обратите внимание на пакеты корреспонденции, у которых статусы не изменялись критически продолжительное время.
        </td>
    </tr>
    <?php foreach ($items as $item): ?>
    <tr>
        <td class="content-block">
            <strong><?= $item['stateName'] ?></strong> (лимит <?= trim(foProjects::downcounter($item['time'])) ?>)
            <p>Проекты: <?= implode(', ', $item['packages']) ?></p>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
