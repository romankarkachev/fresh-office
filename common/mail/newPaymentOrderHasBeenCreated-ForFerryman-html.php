<?php

/* @var $this yii\web\View */
/* @var $amount float */
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый перевозчик!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Просим выставить счет за оказанные транспортные услуги на сумму <?= Yii::$app->formatter->asCurrency($amount) ?>

        </td>
    </tr>
</table>
