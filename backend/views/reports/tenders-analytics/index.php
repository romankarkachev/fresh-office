<?php

use yii\widgets\Pjax;
use common\models\Tenders;
use common\models\reports\TendersAnalytics;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel TendersAnalytics */
/* @var $totalCount integer общее количество закупок */
/* @var $dpTable1 yii\data\ArrayDataProvider */
/* @var $dpTable2 yii\data\ArrayDataProvider */
/* @var $dpTable3 yii\data\ArrayDataProvider */
/* @var $dpTable4 yii\data\ArrayDataProvider */
/* @var $dpTable5 yii\data\ArrayDataProvider */
/* @var $dpTable6 yii\data\ArrayDataProvider */
/* @var $dpTable7 yii\data\ArrayDataProvider */
/* @var $dpTable8 yii\data\ArrayDataProvider */
/* @var $dpTable9 yii\data\ArrayDataProvider */

$this->title = 'Анализ госзакупок | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Анализ госзакупок';

$table7Columns = [
    'table7_name',
];

for ($i = 1; $i <= 4; $i++) {
    $table7Columns[] = [
        'attribute' => 'table7_measure' . ($i > 3 ? 'na' : $i),
        'value' => function($model, $key, $index, $column) {
            /* @var $model \common\models\reports\TendersAnalytics */

            if (!empty($model[$column->attribute])) {
                return $model[$column->attribute];
            }

            return '';
        },
        'headerOptions' => ['class' => 'text-center'],
        'contentOptions' => ['class' => 'text-center'],
        'options' => ['width' => '90'],
    ];
}

$table7Columns[] = [
    'attribute' => 'table7_count',
    'headerOptions' => ['class' => 'text-center'],
    'contentOptions' => ['class' => 'text-center'],
    'options' => ['width' => '90'],
];
?>
<div class="reports-tenders-analytics">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <h4>Всего закупок: <strong><?= $totalCount ?></strong>.</h4>
    <div class="row">
        <!-- <?= TendersAnalytics::CAPTION_FOR_TABLE1 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'pjax-table1', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable1,
                'id' => 'table1',
                'caption' => '<small>' . TendersAnalytics::CAPTION_FOR_TABLE1 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    'tables1_2_name',
                    [
                        'attribute' => 'tables1_2_count',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>

        </div>
        <!-- <?= TendersAnalytics::CAPTION_FOR_TABLE2 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'pjax-table2', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable2,
                'id' => 'table2',
                'caption' => '<small>' . TendersAnalytics::CAPTION_FOR_TABLE2 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    'tables1_2_name',
                    [
                        'attribute' => 'tables1_2_count',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>

        </div>
        <!-- <?= TendersAnalytics::CAPTION_FOR_TABLE3 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'pjax-table3', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable3,
                'id' => 'table3',
                'caption' => '<small>' . TendersAnalytics::CAPTION_FOR_TABLE3 . '</small>',
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
        <!-- <?= TendersAnalytics::CAPTION_FOR_TABLE4 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'pjax-table4', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable4,
                'id' => 'table4',
                'caption' => '<small>' . TendersAnalytics::CAPTION_FOR_TABLE4 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    [
                        'attribute' => 'table4_name',
                        'value' => function($model, $key, $index, $column) {
                            /* @var $model \common\models\reports\TendersAnalytics */

                            if (!empty($model[$column->attribute])) {
                                return \common\models\Tenders::getComplexityCaption($model[$column->attribute]);
                            }
                        },
                    ],
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
        <!-- <?= TendersAnalytics::CAPTION_FOR_TABLE5 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'pjax-table5', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable5,
                'id' => 'table5',
                'caption' => '<small>' . TendersAnalytics::CAPTION_FOR_TABLE5 . '</small>',
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
        <!-- <?= TendersAnalytics::CAPTION_FOR_TABLE6 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'pjax-table6', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable6,
                'id' => 'table6',
                'caption' => '<small>' . TendersAnalytics::CAPTION_FOR_TABLE6 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    [
                        'attribute' => 'table6_name',
                        'value' => function($model, $key, $index, $column) {
                            /* @var $model \common\models\reports\TendersAnalytics */

                            if (!empty($model[$column->attribute])) {
                                return Tenders::getLawCaption($model[$column->attribute]);
                            }
                        },
                    ],
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
        <!-- <?= TendersAnalytics::CAPTION_FOR_TABLE7 ?> -->
        <div class="col-md-5">
            <?php Pjax::begin(['id' => 'pjax-table7', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable7,
                'id' => 'table7',
                'caption' => '<small>' . TendersAnalytics::CAPTION_FOR_TABLE7 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => $table7Columns,
            ]); ?>

            <?php Pjax::end(); ?>

        </div>
    </div>
    <div class="row">
        <!-- <?= TendersAnalytics::CAPTION_FOR_TABLE8 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'pjax-table8', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable8,
                'id' => 'table8',
                'caption' => '<small>' . TendersAnalytics::CAPTION_FOR_TABLE8 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    'table8_name',
                    [
                        'attribute' => 'table8_count',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'options' => ['width' => '90'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>

        </div>
        <!-- <?= TendersAnalytics::CAPTION_FOR_TABLE9 ?> -->
        <div class="col-md-3">
            <?php Pjax::begin(['id' => 'pjax-table9', 'enablePushState' => false, 'timeout' => 5000]); ?>

            <?= GridView::widget([
                'dataProvider' => $dpTable9,
                'id' => 'table9',
                'caption' => '<small>' . TendersAnalytics::CAPTION_FOR_TABLE9 . '</small>',
                'captionOptions' => ['class' => 'text-muted text-right'],
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-condensed'],
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '<em>не выбрано</em>'],
                'columns' => [
                    'table9_name',
                    [
                        'attribute' => 'table9_count',
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
