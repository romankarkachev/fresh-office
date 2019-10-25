<?php

/**
 * Шаблон письма при отправке платежного ордера в отказ. ВНИМАНИЕ! Используется также для тендеров.
 */

/* @var $this yii\web\View */
/* @var $model \common\models\Po|\common\models\Tenders */
$objectKind = 'платежному ордеру';
if ($model instanceof \common\models\Tenders) {
    $objectKind = 'закупке ' . $model->title;
}
else {
    $objectKind .= ' № ' . $model->id;
}
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            По <?= $objectKind ?> от <?= Yii::$app->formatter->asDate($model->created_at, 'php:d.m.Y') ?> получен отказ с формулировкой: <strong><?= nl2br($model->comment) ?></strong>.

        </td>
    </tr>
</table>
