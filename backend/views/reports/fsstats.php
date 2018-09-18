<?php

use backend\components\grid\GridView;
use yii\widgets\Pjax;
use common\models\ReportFileStorageStats;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportFileStorageStats */
/* @var $searchApplied bool */
/* @var $dpTable1 yii\data\ArrayDataProvider */
/* @var $dpTable2 yii\data\ArrayDataProvider */

$this->title = 'Статистика по файловому хранилищу | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Статистика по файловому хранилищу';
?>
<div class="reports-fsstats">
    <?= $this->render('_search_fsstats', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <div class="row">
        <!-- <?= ReportFileStorageStats::CAPTION_FOR_TABLE1 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'pjax-table1', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable1,
                'id' => 'table1',
                'caption' => '<small>' . ReportFileStorageStats::CAPTION_FOR_TABLE1 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                //'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
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
                        'attribute' => 'table1_views',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                    [
                        'attribute' => 'table1_downloads',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                    [
                        'attribute' => 'table1_uploads',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>

        </div>
        <div class="col-md-6">
            <?php Pjax::begin(['id' => 'pjax-table2', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable2,
                'id' => 'table2',
                'caption' => '<small>' . ReportFileStorageStats::CAPTION_FOR_TABLE2 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                //'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>нет данных</em>'],
                'columns' => [
                    'table2_name',
                    [
                        'attribute' => 'table2_views',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                    [
                        'attribute' => 'table2_downloads',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                    [
                        'attribute' => 'table2_uploads',
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
