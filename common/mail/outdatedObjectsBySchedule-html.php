<?php

/* @var yii\web\View $this */
/* @var array $outdatedObjects */
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый получатель!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Обратите внимание на объекты, по которым зарегистрирована просрочка.
        </td>
    </tr>
    <?php foreach ($outdatedObjects as $object): ?>
    <tr>
        <td class="content-block">
            <strong><?= $object['name'] ?></strong>
        </td>
    </tr>
    <?php foreach ($object['items'] as $item): ?>
    <tr>
        <td class="content-block">
            <?= $item ?>

        </td>
    </tr>
    <?php endforeach; ?>
    <?php endforeach; ?>
</table>
