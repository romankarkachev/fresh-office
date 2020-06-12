<?php

/**
 * Шаблон письма при создании тендера менеджером отдела продаж.
 */

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \common\models\Tenders */
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            Создана новая закупка для участия: <?= nl2br($model->title) ?>.
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <?= Html::a('Открыть закупку', Yii::$app->urlManager->createAbsoluteUrl(['/tenders/update', 'id' => $model->id]), ['class' => 'btn-primary']) ?>

            <?= Html::a('Открыть закупку (локально)', 'http://' . Yii::$app->params['serverLocalIp'] . \yii\helpers\Url::toRoute(['/tenders/update', 'id' => $model->id]), ['class' => 'btn-primary']) ?>

        </td>
    </tr>
</table>
