<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TransportSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = 'Транспорт | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Транспорт';
?>
<div class="transport-list">
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            'ferrymanName',
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
            ],
            //'vin',
            //'rn',
            //'trailer_rn',
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{checkTransport} {inspections} {update} {delete}',
                'buttons' => [
                    // кнопка для перехода на сайт ГИБДД для проверки автомобиля по VIN-номеру
                    'checkTransport' => function ($url, $model) {
                        /* @var $model \common\models\Transport */
                        $vin = trim($model->vin);
                        return Html::a('<i class="fa fa-id-card-o"></i>', 'http://www.gibdd.ru/check/auto/#' . $vin,
                            [
                                'title' => Yii::t('yii', 'Проверить автомобиль по VIN-номеру на сайте ГИБДД'),
                                'class' => 'btn btn-xs btn-default',
                                'target' => '_blank',
                            ]);
                    },
                    'inspections' => function ($url, $model) {
                        return Html::a('<i class="fa fa-truck"></i>', ['ferrymen/transports-inspections', 'id' => $model->id], ['title' => Yii::t('yii', 'Техосмотры'), 'class' => 'btn btn-xs btn-default']);
                    },
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-xs btn-default']);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', $url, ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                    }
                ],
                'options' => ['width' => '130'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>
</div>
