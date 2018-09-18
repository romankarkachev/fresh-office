<?php

use backend\components\grid\GridView;
use yii\widgets\Pjax;
use common\models\ReportPbxAnalytics;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportPbxAnalytics */
/* @var $searchApplied bool */
/* @var $dpTable1 yii\data\ArrayDataProvider */
/* @var $dpTable2 yii\data\ArrayDataProvider */
/* @var $dpTable3 yii\data\ArrayDataProvider */
/* @var $dpTable4 yii\data\ArrayDataProvider */
/* @var $dpTable5 yii\data\ArrayDataProvider */

$this->title = 'Статистика по телефонии | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Статистика по телефонии';
?>
<div class="reports-pbxcalls">
    <?= $this->render('_search_pbxcalls', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <div class="row">
        <!-- <?= ReportPbxAnalytics::CAPTION_FOR_TABLE1 ?> -->
        <div class="col-md-2">
            <?php Pjax::begin(['id' => 'pjax-table1', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable1,
                'id' => 'table1',
                'caption' => '<small>' . ReportPbxAnalytics::CAPTION_FOR_TABLE1 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'columns' => [
                    'table1_name',
                    [
                        'attribute' => 'table1_count',
                        'format' => 'decimal',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>

            <?php Pjax::begin(['id' => 'pjax-table5', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable5,
                'id' => 'table5',
                'caption' => '<small>' . ReportPbxAnalytics::CAPTION_FOR_TABLE5 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
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
        <div class="col-md-4">
            <?php Pjax::begin(['id' => 'pjax-table2', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable2,
                'id' => 'table2',
                'caption' => '<small>' . ReportPbxAnalytics::CAPTION_FOR_TABLE2 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'columns' => [
                    'table2_name',
                    [
                        'attribute' => 'measure_all',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                    [
                        'attribute' => 'measure_new',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                    [
                        'attribute' => 'measure_in',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                    [
                        'attribute' => 'measure_out',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>

        </div>
        <div class="col-md-4">
            <?php Pjax::begin(['id' => 'pjax-table3', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable3,
                'id' => 'table3',
                'caption' => '<small>' . ReportPbxAnalytics::CAPTION_FOR_TABLE3 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'columns' => [
                    'table3_name',
                    [
                        'attribute' => 'measure_all',
                        'label' => $searchModel->getAttributeLabel('measure_all') . ' <small><i class="fa fa-exclamation-triangle text-warning" aria-hidden="true" title="Почему показатель &quot;Все&quot; не сходится с суммой входящих и исходящих звонков? Данный показатель включает еще внутренние звонки. Другими словами [Всего] - ([Входящие] + [Исходящие]) = [Количество внутренних]."></i></small>',
                        'encodeLabel' => false,
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                    [
                        'attribute' => 'measure_new',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                    [
                        'attribute' => 'measure_in',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                    [
                        'attribute' => 'measure_out',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>

        </div>
        <div class="col-md-2">
            <?php Pjax::begin(['id' => 'pjax-table4', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable4,
                'id' => 'table4',
                'caption' => '<small>' . ReportPbxAnalytics::CAPTION_FOR_TABLE4 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'columns' => [
                    'table4_name',
                    [
                        'attribute' => 'measure_all',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                    [
                        'attribute' => 'measure_new',
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
