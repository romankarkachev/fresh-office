<?php

use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\ReportTRAnalytics;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportAnalytics */
/* @var $searchApplied bool */
/* @var $avgFinish integer среднее время на закрытие запроса */
/* @var $totalCount integer общее количество запросов */
/* @var $dpTable1 yii\data\ArrayDataProvider */
/* @var $dpTable2 yii\data\ArrayDataProvider */
/* @var $dpTable3 yii\data\ArrayDataProvider */
/* @var $dpTable4 yii\data\ArrayDataProvider */
/* @var $dpTable5 yii\data\ArrayDataProvider */
/* @var $dpTable6 yii\data\ArrayDataProvider */

$this->title = 'Анализ запросов на транспорт | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Анализ запросов на транспорт';

$avgRep = 'Сколько обрабатывается запрос в среднем &mdash; нет данных.';
if ($avgFinish > 0)
    $avgRep = 'Запрос обрабатывается в среднем за <strong>' . \common\models\foProjects::downcounter($avgFinish) . '</strong>.';
?>
<div class="reports-tranalytics">
    <?= $this->render('_search_tranalytics', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <h4>Всего запросов: <strong><?= $totalCount ?></strong>. <?= $avgRep; ?></h4>
    <div class="row">
        <!-- <?= ReportTRAnalytics::CAPTION_FOR_TABLE1 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'table1', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable1,
                'id' => 'table1',
                'caption' => '<small>' . ReportTRAnalytics::CAPTION_FOR_TABLE1 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    // не надо, но если пригодится, то вот:
//                    [
//                        'attribute' => 'table1_id',
//                        'headerOptions' => ['class' => 'text-center'],
//                        'contentOptions' => ['class' => 'text-center'],
//                        'options' => ['width' => '60'],
//                    ],
                    'table1_name',
                    [
                        'attribute' => 'table1_count',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>

        </div>
        <!-- <?= ReportTRAnalytics::CAPTION_FOR_TABLE2 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'table2', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable2,
                'id' => 'table2',
                'caption' => '<small>' . ReportTRAnalytics::CAPTION_FOR_TABLE2 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    'table2_name',
                    [
                        'attribute' => 'table2_count',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>

        </div>
        <!-- <?= ReportTRAnalytics::CAPTION_FOR_TABLE3 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'table3', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable3,
                'id' => 'table3',
                'caption' => '<small>' . ReportTRAnalytics::CAPTION_FOR_TABLE3 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    'table3_name',
                    [
                        'attribute' => 'table3_count',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>

        </div>
        <!-- <?= ReportTRAnalytics::CAPTION_FOR_TABLE4 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'table4', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable4,
                'id' => 'table4',
                'caption' => '<small>' . ReportTRAnalytics::CAPTION_FOR_TABLE4 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    'table4_name',
                    [
                        'attribute' => 'table4_count',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>

        </div>
    </div>
    <div class="row">
        <!-- <?= ReportTRAnalytics::CAPTION_FOR_TABLE5 ?> -->
        <div class="col-md-6">
            <?php Pjax::begin(['id' => 'table5', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable5,
                'id' => 'table5',
                'caption' => '<small>' . ReportTRAnalytics::CAPTION_FOR_TABLE5 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    'table5_name',
                    [
                        'attribute' => 'table5_count',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>

        </div>
        <!-- <?= ReportTRAnalytics::CAPTION_FOR_TABLE6 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'table6', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable6,
                'id' => 'table6',
                'caption' => '<small>' . ReportTRAnalytics::CAPTION_FOR_TABLE6 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    'table6_name',
                    [
                        'attribute' => 'table6_count',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>

        </div>
    </div>
</div>
