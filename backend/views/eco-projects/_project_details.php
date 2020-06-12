<?php
/* @var $this yii\web\View */
/* @var $model common\models\EcoProjects */
?>
<p>
    Проект № <?= $model->id ?> типа <?= $model->typeName ?> создан пользователем <?= $model->createdByProfileName ?> <?= Yii::$app->formatter->asDate($model->created_at, 'php:d F Y г. в H:i') ?>
    <?php if ($model->date_start != Yii::$app->formatter->asDate($model->created_at, 'php:Y-m-d')): ?>
    , старт работ &mdash; <strong><?= Yii::$app->formatter->asDate(strtotime($model->date_start . ' 00:00:00'), 'php:d F Y г.') ?></strong>
    <?php else: ?>
    и сразу запущен в работу.
    <?php endif; ?>
    <br/>
    Заказчик <strong><?= $model->customerName ?></strong>
    <?php if (empty($model->closed_at)): ?>
    должен получить результат не позднее <strong><?= Yii::$app->formatter->asDate(strtotime($model->date_close_plan . ' 00:00:00'), 'php:d F Y г.') ?></strong><?php if (!empty($model->organizationShortName)): ?>,
    исполнением работ занимается <?= $model->organizationShortName ?>.<?php endif; ?>
    <?php else: ?>
    , проект завершен <strong><?= Yii::$app->formatter->asDate(strtotime(date('Y-m-d 00:00:00', $model->closed_at)), 'php:d F Y г.') ?></strong>
    <?php endif; ?>
</p>
