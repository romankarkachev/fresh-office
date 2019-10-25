<?php

use backend\components\grid\GridView;
use yii\widgets\Pjax;
use common\models\ReportEdfAnalytics;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportAnalytics */
/* @var $searchApplied bool */
/* @var $totalCount integer общее количество документов */
/* @var $dpTable1 yii\data\ArrayDataProvider */
/* @var $dpTable2 yii\data\ArrayDataProvider */
/* @var $dpTable3 yii\data\ArrayDataProvider */

$this->title = 'Анализ документооборота | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Анализ документооборота';
?>
<div class="reports-документооборота">
    <?= $this->render('_search_edf', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <h4>Всего документов: <strong><?= $totalCount ?></strong>.</h4>
    <div class="row">
        <!-- <?= ReportEdfAnalytics::CAPTION_FOR_TABLE1 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'pjax-table1', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable1,
                'id' => 'table1',
                'caption' => '<small>' . ReportEdfAnalytics::CAPTION_FOR_TABLE1 . '</small>',
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
        <!-- <?= ReportEdfAnalytics::CAPTION_FOR_TABLE2 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'pjax-table2', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable2,
                'id' => 'table2',
                'caption' => '<small>' . ReportEdfAnalytics::CAPTION_FOR_TABLE2 . '</small>',
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
        <!-- <?= ReportEdfAnalytics::CAPTION_FOR_TABLE3 ?> -->
        <div class="col-md-2">
            <?php Pjax::begin(['id' => 'pjax-table3', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable3,
                'id' => 'table3',
                'caption' => '<small>' . ReportEdfAnalytics::CAPTION_FOR_TABLE3 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    [
                        'attribute' => 'table3_name',
                        'value' => function($model, $key, $index, $column) {
                            /* @var $model \common\models\ReportEdfAnalytics */

                            $measureName = $model[$column->attribute];

                            switch ($measureName) {
                                case 0:
                                    $measureName = 'Нетиповой';
                                    break;
                                case 1:
                                    $measureName = 'Типовой';
                                    break;
                            }

                            return $measureName;
                        },
                    ],
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
    </div>
</div>
