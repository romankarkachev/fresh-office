<?php

use yii\helpers\Html;
use ferryman\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TransportSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Транспорт | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Транспорт';

$this->params['breadcrumbsRight'][] = ['label' => 'Создать', 'icon' => 'fa fa-plus-circle fa-lg', 'url' => ['create'], 'class' => 'btn text-success'];
$this->params['breadcrumbsRight'][] = ['icon' => 'fa fa-sort-amount-asc', 'url' => ['/transport'], 'title' => 'Сбросить отбор и применить сортировку по-умолчанию'];
?>
<div class="transport-list">
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
                                '/transport', 'TransportSearch' => ['ferryman_id' => $model->ferryman_id]
                            ], [
                                'title' => 'Отобрать по этому перевозчику',
                            ]);
                        },
                        'visible' => Yii::$app->user->can('root'),
                    ],
                    'brandName',
                    'ttName',
                    [
                        'header' => 'VIN и госномер',
                        'value' => function ($model, $key, $index, $column) {
                            /** @var $model \common\models\Transport */
                            /** @var $column \yii\grid\DataColumn */

                            $result = $model->vin;
                            if ($model->rn != null && $model->rn != '') $result .= ' г/н ' . $model->rn;
                            if ($model->trailer_rn != null && $model->trailer_rn != '') $result .= ' прицеп ' . $model->trailer_rn;
                            return $result;
                        },
                        'visible' => false,
                    ],
                    [
                        'header' => 'Госномер',
                        'value' => function ($model, $key, $index, $column) {
                            /** @var $model \common\models\Transport */
                            /** @var $column \yii\grid\DataColumn */

                            $result = '';
                            if ($model->rn != null && $model->rn != '') $result .= ' г/н ' . $model->rn;
                            if ($model->trailer_rn != null && $model->trailer_rn != '') $result .= ' прицеп ' . $model->trailer_rn;
                            return $result;
                        },
                    ],
                    [
                        'attribute' => 'inspDetails',
                        'value' => function($model, $key, $index, $column) {
                            /* @var $model \common\models\Transport */
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
