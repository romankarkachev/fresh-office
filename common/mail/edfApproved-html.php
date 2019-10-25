<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $edf \common\models\Edf */
/* @var $body string текст письма */

$link = Yii::$app->urlManager->createAbsoluteUrl(['/edf/update', 'id' => $edf->id]);
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

        </td>
    </tr>
    <tr>
        <td class="content-block">
            <?= Html::a('Открыть документ', $link, ['class' => 'btn-primary']) ?>

        </td>
    </tr>
</table>
