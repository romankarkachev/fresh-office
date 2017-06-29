<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\Ferrymen */
/* @var $dpDrivers common\models\Drivers[] */
?>
<div class="page-header"><h3>Водители  <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i> Добавить водителя', ['/ferrymen/create-driver'], [
    'id' => 'btn-add-driver',
    'class' => 'btn btn-default',
    'data-params' => ['ferryman_id' => $model->id],
    'data-method' => 'post',
]) ?></h3></div>
<div class="ferryman-drivers">
    <?= GridView::widget([
        'dataProvider' => $dpDrivers,
        'layout' => '{items}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'attribute' => 'surname',
                'label' => 'ФИО',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /** @var $model \common\models\Drivers */
                    /** @var $column \yii\grid\DataColumn */

                    $result = '';
                    $result = $model->surname . ' ';
                    $result .= ' ' . $model->name;
                    $result = trim($result);
                    $result .= ' ' . $model->patronymic;
                    $result = trim($result);

                    $result .= '<p>' . Html::a('Инструктажи &rarr;', ['/ferrymen/drivers-instructings', 'id' => $model->id]) . '</p>';

                    return $result;
                },
            ],
            [
                'attribute' => 'driver_license',
                'label' => 'Вод. удост.',
            ],
            [
                'attribute' => 'phone',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\Drivers */
                    return \common\models\Ferrymen::normalizePhone($model->{$column->attribute});
                }
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', ['/ferrymen/delete-driver', 'id' => $model->id], [
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
