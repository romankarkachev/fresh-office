<?php

use yii\helpers\Html;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\Ferrymen */
/* @var $dpTransport common\models\Transport[] */
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <h4>Транспорт  <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i> Добавить ТС', ['/ferrymen/create-transport'], [
            'id' => 'btn-add-transport',
            'class' => 'btn btn-default',
            'title' => 'Добавить транспортное средство',
            'data-params' => ['ferryman_id' => $model->id],
            'data-method' => 'post',
        ]) ?></h4>
    </div>
    <div class="panel-body">
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
                    'stateName',
                    [
                        'label' => 'ДОПОГ',
                        'format' => 'raw',
                        'value' => function($model, $key, $index, $column) {
                            /* @var $model \common\models\Transport */
                            /* @var $column \yii\grid\DataColumn */

                            $options = ['disabled' => true];
                            if (!empty($model->is_dopog)) {
                                $options['checked'] = 'checked';
                            }

                            return Html::input('checkbox', 'Transport[is_dopog]', 1, $options);
                        },
                        'options' => ['width' => '30'],
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                    ],
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
    </div>
</div>
