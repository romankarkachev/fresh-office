<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\CorrespondencePackages */

$link = Yii::$app->urlManager->createAbsoluteUrl(['/correspondence-packages/update', 'id' => $model->id]);
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый менеджер!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Создан пакет корреспонденции, в котором Вы указаны как ответственный.
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <?= Html::a('Открыть пакет', $link, ['class' => 'btn-primary']) ?>

        </td>
    </tr>
</table>
