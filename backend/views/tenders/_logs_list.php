<?php

use yii\widgets\Pjax;
use common\models\TendersLogs;
use common\models\TendersLogsSearch;

/* @var $this yii\web\View */
/* @var $searchModel \common\models\TendersLogsSearch */
/* @var $dataProvider \yii\data\ActiveDataProvider of common\models\TendersLogs
 */
?>
<?php Pjax::begin(['id' => 'pjax-logs', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

<?= $this->render('_search_logs', ['model' => $searchModel]); ?>

<?= \backend\components\grid\GridView::widget([
    'id' => TendersLogs::DOM_IDS['GRIDVIEW_ID'],
    'dataProvider' => $dataProvider,
    'layout' => '{items}',
    'tableOptions' => ['class' => 'table table-striped table-hover'],
    'columns' => [
        [
            'attribute' => 'created_at',
            'label' => 'Создано',
            'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
            'options' => ['width' => '130'],
        ],
        [
            'attribute' => 'createdByProfileName',
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
            'options' => ['width' => '200'],
        ],
        [
            'attribute' => 'type',
            'label' => 'Источник',
            'value' => function($model, $key, $index, $column) {
                /* @var $model \common\models\TendersLogs */
                /* @var $column \yii\grid\DataColumn */

                return $model->typeName;
            },
            'visible' => $searchModel->type == TendersLogsSearch::FILTER_PROGRESS_ВСЕ,
        ],
        [
            'attribute' => 'description',
            'format' => 'raw',
            'value' => function($model, $key, $index, $column) {
                /* @var $model \common\models\TendersLogs */
                /* @var $column \yii\grid\DataColumn */

                return nl2br($model->{$column->attribute});
            },
        ]
    ],
]); ?>

<?php Pjax::end(); ?>
