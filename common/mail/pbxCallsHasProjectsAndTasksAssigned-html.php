<?php

/* @var $this yii\web\View */
/* @var $operatingDate string */
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Во вложении файл с информацией по наличию проектов и задач контрагентов, звонки от которых поступали
            <?= Yii::$app->formatter->asDate($operatingDate, 'php:d F Y'); ?>.
        </td>
    </tr>
</table>
