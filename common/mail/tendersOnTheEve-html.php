<?php

use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var array $tenders */
/* @var string $date дата аукциона */
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый специалист по тендерам!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Обратите внимание на закупки, аукцион по которым назначен на завтра, <?= $date ?>.
        </td>
    </tr>
    <?php foreach ($tenders as $tender): ?>
    <tr>
        <td class="content-block">
            Закупка № <?= $tender['oos_number'] ?><?= !empty($tender['title']) ? ' (' . \yii\helpers\StringHelper::truncate($tender['title'], 50) . ')' : '' ?>:
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <?= Html::a('Открыть тендер', Yii::$app->urlManager->createAbsoluteUrl(['/tenders/update', 'id' => $tender['tender_id']]), ['class' => 'btn-primary']) ?>

            <?= Html::a('Открыть закупку (локально)', 'http://' . Yii::$app->params['serverLocalIp'] . \yii\helpers\Url::toRoute(['/tenders/update', 'id' => $tender['tender_id']]), ['class' => 'btn-primary']) ?>

        </td>
    </tr>
    <?php endforeach; ?>
</table>
