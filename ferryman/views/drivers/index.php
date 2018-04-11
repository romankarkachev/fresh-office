<?php

use yii\helpers\Html;
use ferryman\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\DriversSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Водители | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Водители';

$this->params['breadcrumbsRight'][] = ['label' => 'Создать', 'icon' => 'fa fa-plus-circle fa-lg', 'url' => ['create'], 'class' => 'btn text-success'];
$this->params['breadcrumbsRight'][] = ['icon' => 'fa fa-sort-amount-asc', 'url' => ['/drivers'], 'title' => 'Сбросить отбор и применить сортировку по-умолчанию'];
?>
<div class="drivers-list">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <div class="card">
        <div class="card-block">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'ferrymanName',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $column) {
                            /* @var $model \common\models\Drivers */
                            /* @var $column \yii\grid\DataColumn */

                            return Html::a($model->{$column->attribute}, [
                                '/drivers', 'DriversSearch' => ['ferryman_id' => $model->ferryman_id]
                            ], [
                                'title' => 'Отобрать по этому перевозчику',
                            ]);
                        },
                        'visible' => Yii::$app->user->can('root'),
                    ],
                    [
                        'attribute' => 'surname',
                        'label' => 'ФИО',
                        'value' => function ($model) {
                            /* @var $model \common\models\Drivers */
                            /* @var $column \yii\grid\DataColumn */

                            return $model->surname . ' ' . $model->name . ' ' . $model->patronymic;
                        },
                    ],
                    [
                        'header' => 'Паспорт',
                        'value' => function ($model) {
                            /* @var $model \common\models\Drivers */
                            /* @var $column \yii\grid\DataColumn */

                            $result = '';
                            if ($model->pass_serie != null)
                                $result .= $model->pass_serie;
                            if ($model->pass_num != null)
                                $result .= ' № ' . $model->pass_num;

                            return $result;
                        },
                    ],
                    'driver_license',
                    [
                        'attribute' => 'phone',
                        'value' => function($model, $key, $index, $column) {
                            /* @var $model \common\models\Drivers */
                            /* @var $column \yii\grid\DataColumn */

                            return \common\models\Drivers::normalizePhoneNumber($model->{$column->attribute});
                        }
                    ],
                    [
                        'attribute' => 'instrCount',
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'visible' => false,
                    ],
                    [
                        'attribute' => 'instrDetails',
                        'value' => function($model, $key, $index, $column) {
                            /* @var $model \common\models\Drivers */
                            /* @var $column \yii\grid\DataColumn */

                            return nl2br($model->{$column->attribute});
                        }
                    ],
                    ['class' => 'ferryman\components\grid\ActionColumn'],
                ],
            ]); ?>

        </div>
    </div>
</div>
