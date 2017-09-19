<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\Ferrymen */
/* @var $dpBankCards common\models\FerrymenBankCards[] */
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <h4>Банковские карты  <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i> Добавить карту', ['/ferrymen/create-bank-card'], [
            'id' => 'btn-add-bank-card',
            'class' => 'btn btn-default',
            'data-params' => ['ferryman_id' => $model->id],
            'data-method' => 'post',
        ]) ?></h4>
    </div>
    <div class="panel-body">
        <div class="ferryman-bank-details">
            <?= GridView::widget([
                'dataProvider' => $dpBankCards,
                'layout' => '{items}',
                'tableOptions' => ['class' => 'table table-striped table-hover'],
                'columns' => [
                    'number',
                    'cardholder',
                    'bank',
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'Действия',
                        'template' => '{update} {delete}',
                        'buttons' => [
                            'update' => function ($url, $model) {
                                return Html::a('<i class="fa fa-pencil"></i>', ['/ferrymen-bank-cards/update', 'id' => $model->id], [
                                    'title' => Yii::t('yii', 'Редактировать'),
                                    'class' => 'btn btn-xs btn-default',
                                ]);
                            },
                            'delete' => function ($url, $model) {
                                return Html::a('<i class="fa fa-trash-o"></i>', ['/ferrymen/delete-bank-card', 'id' => $model->id], [
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
