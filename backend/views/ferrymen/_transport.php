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
            [
                'attribute' => 'vin',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /** @var $model \common\models\Transport */
                    /** @var $column \yii\grid\DataColumn */

                    $result = $model->{$column->attribute};
                    $result .= '<p>' . Html::a('Техосмотры &rarr;', ['/ferrymen/transports-inspections', 'id' => $model->id]) . '</p>';
                    return $result;
                },
            ],
            'rn',
            'trailer_rn',
            'ttName',
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{delete}',
                'buttons' => [
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
                'options' => ['width' => '40'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>
