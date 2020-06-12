<?php

use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var array $tenders */
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый специалист по тендерам!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            С момента последнего обновления информации в тендерах произошли некоторые изменения.
        </td>
    </tr>
    <?php foreach ($tenders as $tender): ?>
    <tr>
        <td class="content-block">
            Новые файлы по закупке № <?= $tender['oos_number'] ?><?= !empty($tender['title']) ? ' (' . \yii\helpers\StringHelper::truncate($tender['title'], 50) . ')' : '' ?>:
            <ul>
                <?php foreach ($tender['files'] as $file): ?>
                <li><?= $file['fn'] ?></li>
                <?php endforeach; ?>
            </ul>
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
