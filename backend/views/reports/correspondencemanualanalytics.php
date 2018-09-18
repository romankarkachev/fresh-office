<?php

use backend\components\grid\GridView;
use yii\widgets\Pjax;
use common\models\ReportCorrespondenceAnalytics;
use common\models\foProjects;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportAnalytics */
/* @var $searchApplied bool */
/* @var $avgFinish integer среднее время на подготовку отправления (Формирование на отправку минус Отдано на отправку */
/* @var $totalCount integer общее количество запросов */
/* @var $dpTable1 yii\data\ArrayDataProvider */
/* @var $dpTable2 yii\data\ArrayDataProvider */
/* @var $dpTable3 yii\data\ArrayDataProvider */
/* @var $dpTable4 yii\data\ArrayDataProvider */

$this->title = 'Анализ корреспонденции, созданной вручную | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Анализ корреспонденции, созданной вручную';

$avgRep = 'Сколько в среднем требуется на подготовку &mdash; нет данных.';
if ($avgFinish > 0)
    $avgRep = 'На подготовку уходит в среднем <strong>' . \common\models\foProjects::downcounter($avgFinish) . '</strong>.';
?>
<div class="reports-correspondencemanualanalytics">
    <?= $this->render('_search_correspondencemanualanalytics', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <h4>Всего пакетов документов: <strong><?= $totalCount ?></strong>. <?= $avgRep; ?></h4>
    <div class="row">
        <!-- <?= ReportCorrespondenceAnalytics::CAPTION_FOR_TABLE1 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'pjax-table1', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable1,
                'id' => 'table1',
                'caption' => '<small>' . ReportCorrespondenceAnalytics::CAPTION_FOR_TABLE1 . '</small>',
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
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'pjax-table2', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable2,
                'id' => 'table2',
                'caption' => '<small>' . ReportCorrespondenceAnalytics::CAPTION_FOR_TABLE22 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    'table22_name',
                    [
                        'attribute' => 'table22_count',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                    [
                        'attribute' => 'table22_rejects_count',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>

        </div>
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'pjax-table3', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable3,
                'id' => 'table3',
                'caption' => '<small>' . ReportCorrespondenceAnalytics::CAPTION_FOR_TABLE23 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    'table23_name',
                    [
                        'attribute' => 'table23_count',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>

        </div>
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'pjax-table4', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable4,
                'id' => 'table4',
                'caption' => '<small>' . ReportCorrespondenceAnalytics::CAPTION_FOR_TABLE24 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    'table24_name',
                    [
                        'attribute' => 'table24_value',
                        'value' => function($model, $key, $index, $column) {
                            /* @var $model \common\models\ReportCorrespondenceAnalytics */

                            return foProjects::downcounter($model[$column->attribute]);
                        },
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>

        </div>
    </div>
</div>
