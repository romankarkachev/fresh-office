<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $appeal common\models\Appeals */

$link = Yii::$app->urlManager->createAbsoluteUrl(['/appeals/update', 'id' => $appeal->id]);
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый менеджер!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Зарегистрировано обращение, в котором Вы указаны как ответственное лицо. Клиент <?= $appeal->fo_company_name ?>, id <strong><?= $appeal->fo_id_company ?></strong>, статус <?= $appeal->caStateName ?>. Во вложении файлы по заявке. Удачной работы!
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <?= Html::a('Открыть обращение', $link, ['class' => 'btn-primary']) ?>

        </td>
    </tr>
</table>
