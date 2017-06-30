<?php

/* @var $this yii\web\View */
/* @var $project array */

$amount = $project['amount'] != null ? $project['amount'] : 0;
$cost = $project['cost'] != null ? $project['cost'] : 0;
$margin = $amount - $cost;
?>
<hr/>
<h3>Проект <?= $project['id'] ?></h3>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Контрагент:</strong> <?= $project['ca_name'] ?>
        </td>
        <td class="content-block">
            <strong>Код:</strong> <?= $project['id'] ?>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <strong>Контактное лицо:</strong> <?= $project['contact_name'] ?>
        </td>
        <td class="content-block">
            <strong>Телефон:</strong> <?= $project['contact_phone'] ?>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <strong>Автор:</strong> <?= $project['author_name'] ?>
        </td>
        <td class="content-block">
            <strong>Ответственный:</strong> <?= $project['manager_name'] ?>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <strong>Стоимость:</strong> <?= Yii::$app->formatter->asCurrency($amount) ?>
        </td>
        <td class="content-block">
            <strong>Статус:</strong> <?= $project['state_name'] ?>
        </td>
    </tr>
</table>
<?php if ($project['comment'] != null): ?>
<p><?= nl2br($project['comment']) ?></p>
<?php endif; ?>

<?php if ($project['properties'] != null): ?>
<h4>Параметры проекта</h4>
<table class="invoice" width="100%" cellpadding="0" cellspacing="0">
    <?php foreach($project['properties'] as $property): ?>
    <tr>
        <td><strong><?= $property['property'] ?></strong></td>
        <td><?= $property['value'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
