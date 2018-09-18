<?php

use backend\components\grid\GridView;
use yii\widgets\Pjax;
use common\models\ReportAnalytics;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportAnalytics */
/* @var $searchApplied bool */
/* @var $totalAppealsPeriod integer */
/* @var $totalAppeals integer */
/* @var $dpTable1 yii\data\ArrayDataProvider */
/* @var $dpTable2 yii\data\ArrayDataProvider */
/* @var $dpTable3 yii\data\ArrayDataProvider */
/* @var $dpTable4 yii\data\ArrayDataProvider */
/* @var $dpTable5 yii\data\ArrayDataProvider */
/* @var $columns5 array колонки для таблицы 5 */
/* @var $dpTable6 yii\data\ArrayDataProvider */
/* @var $columns6 array колонки для таблицы 6 */

$this->title = 'Анализ обращений клиентов | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Анализ обращений клиентов';
?>
<div class="reports-analytics">
    <?= $this->render('_search_analytics', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <h4>Количество обращений за период: <strong><?= $totalAppealsPeriod ?></strong>.</h4>
                <!-- <?= ReportAnalytics::CAPTION_FOR_TABLE1 ?> -->
                <div class="col-md-6">
                    <?php Pjax::begin(['id' => 'table1', 'enablePushState' => false, 'timeout' => 5000]); ?>

                    <?= GridView::widget([
                        'dataProvider' => $dpTable1,
                        'id' => 'table1',
                        'caption' => '<small>' . ReportAnalytics::CAPTION_FOR_TABLE1 . '</small>',
                        'captionOptions' => ['class' => 'text-muted text-right'],
                        'layout' => '{items}',
                        'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                        'columns' => [
                            // не надо, но если пригодится, то вот:
//                            [
//                                'attribute' => 'table1_id',
//                                'headerOptions' => ['class' => 'text-center'],
//                                'contentOptions' => ['class' => 'text-center'],
//                                'options' => ['width' => '60'],
//                            ],
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
                <!-- <?= ReportAnalytics::CAPTION_FOR_TABLE2 ?> -->
                <div class="col-md-6">
                    <?php Pjax::begin(['id' => 'table2', 'enablePushState' => false, 'timeout' => 5000]); ?>

                    <?= GridView::widget([
                        'dataProvider' => $dpTable2,
                        'id' => 'table2',
                        'caption' => '<small>' . ReportAnalytics::CAPTION_FOR_TABLE2 . '</small>',
                        'captionOptions' => ['class' => 'text-muted text-right'],
                        'layout' => '{items}',
                        'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
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
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <h4>Всего обращений: <strong><?= $totalAppeals ?></strong>.</h4>
                <!-- <?= ReportAnalytics::CAPTION_FOR_TABLE3 ?> -->
                <div class="col-md-6">
                    <?php Pjax::begin(['id' => 'table3', 'enablePushState' => false, 'timeout' => 5000]); ?>

                    <?= GridView::widget([
                        'dataProvider' => $dpTable3,
                        'id' => 'table3',
                        'caption' => '<small>' . ReportAnalytics::CAPTION_FOR_TABLE3 . '</small>',
                        'captionOptions' => ['class' => 'text-muted text-right'],
                        'layout' => '{items}',
                        'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
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
                <!-- <?= ReportAnalytics::CAPTION_FOR_TABLE4 ?> -->
                <div class="col-md-6">
                    <?php Pjax::begin(['id' => 'table4', 'enablePushState' => false, 'timeout' => 5000]); ?>

                    <?= GridView::widget([
                        'dataProvider' => $dpTable4,
                        'id' => 'table4',
                        'caption' => '<small>' . ReportAnalytics::CAPTION_FOR_TABLE4 . '</small>',
                        'captionOptions' => ['class' => 'text-muted text-right'],
                        'layout' => '{items}',
                        'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
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
        </div>
    </div>
    <div class="form-group">
        <!-- <?= ReportAnalytics::CAPTION_FOR_TABLE5 ?> -->
        <?php Pjax::begin(['id' => 'table5', 'enablePushState' => false, 'timeout' => 5000]); ?>

        <?= GridView::widget([
            'dataProvider' => $dpTable5,
            'id' => 'table5',
            'caption' => '<small>' . ReportAnalytics::CAPTION_FOR_TABLE5 . '</small>',
            'captionOptions' => ['class' => 'text-muted text-right'],
            'layout' => '{items}',
            'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
            'showFooter' => true,
            'footerRowOptions' => ['class' => 'text-center'],
            'columns' => $columns5,
        ]); ?>

        <?php Pjax::end(); ?>

    </div>
    <div class="form-group">
        <!-- <?= ReportAnalytics::CAPTION_FOR_TABLE6 ?> -->
        <?php Pjax::begin(['id' => 'table6', 'enablePushState' => false, 'timeout' => 5000]); ?>

        <?= GridView::widget([
            'dataProvider' => $dpTable6,
            'id' => 'table6',
            'caption' => '<small>' . ReportAnalytics::CAPTION_FOR_TABLE6 . '</small>',
            'captionOptions' => ['class' => 'text-muted text-right'],
            'layout' => '{items}',
            'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
            'showFooter' => true,
            'footerRowOptions' => ['class' => 'text-center'],
            'columns' => $columns6,
        ]); ?>

        <?php Pjax::end(); ?>

    </div>
</div>
