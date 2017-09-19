<?php
/* @var $project array */
?>
<table class="body-wrap">
    <tr>
        <td></td>
        <td class="container" width="800">
            <div class="content">
                <table class="main" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="alert-alert-good">
                            Проект <strong><?= $project['id'] ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td class="content-wrap">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="content-block">
                                        <strong>Контрагент:</strong> <?= $project['ca_name'] ?>
                                    </td>
                                    <td class="content-block">
                                        <strong>Тип проекта:</strong> <?= $project['type_name'] ?>
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
                                        <strong>Ответственный:</strong> <?= $project['manager_name'] ?>
                                    </td>
                                    <td class="content-block">
                                        <strong>Перевозчик:</strong> <?= $project['ferryman'] ?>
                                    </td>
                                </tr>
                            </table>
                            <?php if ($project['comment'] != null): ?>
                                <p><?= nl2br($project['comment']) ?></p>
                            <?php endif; ?>

                            <?php if ($project['properties'] != null): ?>
                                <h4>Параметры проекта</h4>
                                <table class="invoice" width="100%" cellpadding="0" cellspacing="0">
                                    <?php
                                    foreach($project['properties'] as $property) {
                                        if (strpos($property['property'], 'Оплата ТС') !== false) continue;
                                    ?>
                                        <tr>
                                            <td><strong><?= $property['property'] ?></strong></td>
                                            <td><?= $property['value'] ?></td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            <?php endif; ?>

                            <?php if ($project['tp'] != null): ?>
                                <h4>Товары и услуги</h4>
                                <table class="invoice" width="100%" cellpadding="0" cellspacing="0">
                                    <?php foreach($project['tp'] as $property): ?>
                                        <tr>
                                            <td><strong><?= $property['property'] ?></strong></td>
                                            <td><?= $property['value'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                <div class="footer">
                    <table width="100%">
                        <tr>
                            <td class="aligncenter content-block">Письмо создано автоматически, и на него не нужно отвечать.</td>
                        </tr>
                    </table>
                </div>
            </div>
        </td>
        <td></td>
    </tr>
</table>
