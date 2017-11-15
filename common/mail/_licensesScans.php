<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $file \common\models\LicensesFiles */
/* @var $lr_id integer */
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block" style="color:#fff;">
            <?= $lr_id ?>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <?= Html::img($file->fileFfp); ?>

        </td>
    </tr>
</table>
