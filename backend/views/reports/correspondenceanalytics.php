<?php

use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\ReportCorrespondenceAnalytics;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportAnalytics */
/* @var $searchApplied bool */
/* @var $avgCreatedTillReady integer среднее время на подготовку пакета документов */
/* @var $avgReadyTillSent integer среднее время на отправку пакета документов */
/* @var $totalCount integer общее количество запросов */
/* @var $dpTable1 yii\data\ArrayDataProvider */
/* @var $dpTable2 yii\data\ArrayDataProvider */
/* @var $dpTable3 yii\data\ArrayDataProvider */
/* @var $dpTable4 yii\data\ArrayDataProvider */
/* @var $dpTable5 yii\data\ArrayDataProvider */
/* @var $dpTable6 yii\data\ArrayDataProvider */

$this->title = 'Анализ корреспонденции | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Анализ корреспонденции';

$avgReadyRep = 'Сколько подготавливаются пакеты документов в среднем &mdash; нет данных.';
if ($avgCreatedTillReady > 0)
    $avgReadyRep = 'Документы подготавливаются в среднем за <strong>' . \common\models\foProjects::downcounter($avgCreatedTillReady) . '</strong>.';

$avgSentRep = 'Сколько отправляются пакеты документов в среднем &mdash; нет данных.';
if ($avgReadyTillSent > 0)
    $avgSentRep = 'Документы отправляются в среднем за <strong>' . \common\models\foProjects::downcounter($avgReadyTillSent) . '</strong>.';
?>
<div class="reports-correspondenceanalytics">
    <?= $this->render('_search_correspondenceanalytics', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <h4>Всего пакетов документов: <strong><?= $totalCount ?></strong>. <?= $avgReadyRep; ?>. <?= $avgSentRep; ?></h4>
    <div class="row">
        <!-- <?= ReportCorrespondenceAnalytics::CAPTION_FOR_TABLE1 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'table1', 'enablePushState' => false, 'timeout' => 5000]); ?>

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
    </div>
</div>
