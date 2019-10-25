<?php

/**
 * Шаблон письма при согласовании тендера руководством.
 */

/* @var $this yii\web\View */
/* @var $model \common\models\Tenders */

$link = Yii::$app->urlManager->createAbsoluteUrl(['/tenders/update', 'id' => $model->id]);
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый тендерный отдел!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Руководством согласована новая закупка для участия: <?= nl2br($model->title) ?>.
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <?= \yii\helpers\Html::a('Открыть закупку', $link, ['class' => 'btn-primary']) ?>

        </td>
    </tr>
</table>
