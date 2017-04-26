<?php

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportAnalytics */

$this->title = 'Анализ обращений клиентов | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Анализ обращений клиентов';
?>
<div class="reports-analytics-nodata">
    <?= $this->render('_search_analytics', ['model' => $searchModel, 'searchApplied' => false]); ?>

    <p>Выполните настройку отчета.</p>
</div>
