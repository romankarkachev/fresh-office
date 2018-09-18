<?php

use yii\helpers\Html;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\foProjectsSearch */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $isDetailed bool */
/* @var $searchApplied bool */

$this->title = 'Оценки проектов | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Оценки проектов';

if ($isDetailed) {
    $columns = [
        'caName',
        'project_id',
        [
            'attribute' => 'rate',
            'header' => 'Оценка',
            'format' => 'raw',
            'value' => function ($model, $key, $index, $column) {
                /* @var $model \common\models\ProjectsRatings */
                /* @var $column \yii\grid\DataColumn */

                $comment = '';
                $rate = $model->{$column->attribute};
                if ($rate < 5) {
                    $comment = $model->comment;
                }

                return \kartik\rating\StarRating::widget([
                        'name' => 'rating_' . $rate,
                        'value' => $rate,
                        'pluginOptions' => [
                            'size' => 'xs',
                            'displayOnly' => true,
                        ],
                    ]) . $comment;
            },
        ],
        'rated_at:datetime',
        'ratedByProfileName',
    ];
}
else {
    $columns = [
        'caName',
        [
            'attribute' => 'rate',
            'header' => 'Средняя оценка',
            'format' => 'raw',
            'value' => function ($model, $key, $index, $column) {
                /* @var $model \common\models\ProjectsRatings */
                /* @var $column \yii\grid\DataColumn */

                $rate = $model->{$column->attribute};

                return \kartik\rating\StarRating::widget([
                    'name' => 'rating_' . $rate,
                    'value' => $rate,
                    'pluginOptions' => [
                        'size' => 'xs',
                        'displayOnly' => true,
                    ],
                ]);
            },
        ],
        [
            'attribute' => 'ratesCount',
            'value' => function ($model, $key, $index, $column) {
                /* @var $model \common\models\ProjectsRatings */
                /* @var $column \yii\grid\DataColumn */

                return $model->{$column->attribute} . ', средняя: ' . Yii::$app->formatter->asDecimal($model->rate, 2) . '.';
            },
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
            'options' => ['width' => '200'],
        ]
    ];
}
?>
<div class="projects-list">
    <?= $this->render('_search_ratings', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'id' => 'gw-projects-ratings',
        'layout' => '{summary}{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => $columns,
    ]); ?>

</div>
<?php
//$urlCreatePaymentOrderBySelection = Url::to(['/projects/create-order-by-selection']);

$this->registerJs(<<<JS
$("input").iCheck({checkboxClass: "icheckbox_square-green"});
JS
, \yii\web\View::POS_READY);
?>
