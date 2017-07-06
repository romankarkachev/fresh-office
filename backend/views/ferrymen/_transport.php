<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\Ferrymen */
/* @var $dpTransport common\models\Transport[] */
?>
<div class="page-header"><h3>Транспорт  <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i> Добавить ТС', ['/ferrymen/create-transport'], [
    'id' => 'btn-add-transport',
    'class' => 'btn btn-default',
    'title' => 'Добавить транспортное средство',
    'data-params' => ['ferryman_id' => $model->id],
    'data-method' => 'post',
]) ?></h3></div>
<div class="ferryman-transport">
    <?= GridView::widget([
        'dataProvider' => $dpTransport,
        'layout' => '{items}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            'ttName',
            'brandName',
            'rn',
            [
                'attribute' => 'vin',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /** @var $model \common\models\Transport */
                    /** @var $column \yii\grid\DataColumn */

                    $result = $model->{$column->attribute};

                    // количество техсмотров
                    $inspCount = $model->inspCount == null || $model->inspCount == 0 ? '' : ' (<strong>' . $model->inspCount . '</strong>)';

                    $result .= '<p>' . Html::a('Техосмотры' . $inspCount . ' &rarr;', ['/ferrymen/transports-inspections', 'id' => $model->id]) . '</p>';
                    return $result;
                },
            ],
            'trailer_rn',
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', ['/ferrymen-transport/update', 'id' => $model->id], [
                            'title' => Yii::t('yii', 'Редактировать'),
                            'class' => 'btn btn-xs btn-default',
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', ['/ferrymen/delete-transport', 'id' => $model->id], [
                            'class' => 'btn btn-xs btn-danger',
                            'title' => Yii::t('yii', 'Удалить'),
                            'aria-label' => Yii::t('yii', 'Delete'),
                            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                            'data-method' => 'post',
                            'data-pjax' => '0'
                        ]);
                    }
                ],
                'options' => ['width' => '80'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>
