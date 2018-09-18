<?php

use common\models\foProjects;

/* @var $projects array массив проектов со значительно просроченными статусами */
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
    <?php foreach ($projects as $project): ?>
    <tr>
        <td class="content-block">
            <strong><?= $project['stateName'] ?></strong> (<?= trim(foProjects::downcounter($project['time'])) ?>)
            <p>Проекты: <?= implode(', ', $project['projects']) ?></p>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
