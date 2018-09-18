<?php

use backend\components\grid\GridView;
use yii\widgets\Pjax;
use common\models\ReportCorrespondenceAnalytics;
use common\models\foProjects;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportAnalytics */
/* @var $searchApplied bool */
/* @var $totalCount integer общее количество запросов */
/* @var $dpTable1 yii\data\ArrayDataProvider */
/* @var $dpTable2 yii\data\ArrayDataProvider */
/* @var $dpTable3 yii\data\ArrayDataProvider */

$this->title = 'Анализ корреспонденции | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Анализ корреспонденции';
?>
<div class="reports-correspondenceanalytics">
    <?= $this->render('_search_correspondenceanalytics', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <h4>Всего пакетов документов: <strong><?= $totalCount ?></strong>.</h4>
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
                'caption' => '<small>' . ReportCorrespondenceAnalytics::CAPTION_FOR_TABLE2 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    'table2_name',
                    [
                        'attribute' => 'table2_value',
                        'value' => function($model, $key, $index, $column) {
                            /* @var $model \common\models\ReportCorrespondenceAnalytics */

                            return foProjects::downcounter($model[$column->attribute]);
                        },
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
                'caption' => '<small>' . ReportCorrespondenceAnalytics::CAPTION_FOR_TABLE3 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    'table3_name',
                    [
                        'attribute' => 'table3_value',
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
