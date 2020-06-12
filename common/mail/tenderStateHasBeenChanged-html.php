<?php

/**
 * Шаблон письма при согласовании тендера руководством. Также используется при изменении на один из статусов "Дозапрос",
 * "Победа", "Проигрыш".
 */

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \common\models\Tenders */
/* @var $mean string  */
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый тендерный отдел!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <?= $mean ?><?= nl2br($model->title) ?>.
        </td>
    </tr>
    <?php if ($model->state_id == \common\models\TendersStates::STATE_СОГЛАСОВАНА): ?>
    <tr>
        <td class="content-block">
            <?php if (!empty($model->org_id)): ?>
            <p>От кого участвуем: <strong><?= $model->orgName ?></strong></p>
            <?php endif; ?>
            <?php if (!empty($model->conditions)): ?>
            <p>Особые условия: <strong><?= Yii::$app->formatter->asNtext($model->conditions) ?></strong></p>
            <?php endif; ?>
            <?php if (!empty($model->is_contract_edit) && !empty($model->contract_comments)): ?>
            <p>Изменения в договор: <strong><?= Yii::$app->formatter->asNtext($model->contract_comments) ?></strong></p>
            <?php endif; ?>
            <p>Наша цена: <strong><?= Yii::$app->formatter->asDecimal($model->amount_offer) ?></strong></p>
            <?php if (!empty($model->comments)): ?>
            <p>Комментарий: <strong><?= Yii::$app->formatter->asNtext($model->comments) ?></strong></p>
            <?php endif; ?>
        </td>
    </tr>
    <?php endif; ?>
    <tr>
        <td class="content-block">
            <?= Html::a('Открыть закупку', Yii::$app->urlManager->createAbsoluteUrl(['/tenders/update', 'id' => $model->id]), ['class' => 'btn-primary']) ?>

            <?= Html::a('Открыть закупку (локально)', 'http://' . Yii::$app->params['serverLocalIp'] . \yii\helpers\Url::toRoute(['/tenders/update', 'id' => $model->id]), ['class' => 'btn-primary']) ?>

        </td>
    </tr>
</table>
