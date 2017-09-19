<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\Ferrymen */
/* @var $dpBankDetails common\models\FerrymenBankDetails[] */
?>
<div class="page-header"><h3>Банковские счета  <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i> Добавить счет', ['/ferrymen/create-bank-account'], [
    'id' => 'btn-add-bank-account',
    'class' => 'btn btn-default',
    'data-params' => ['ferryman_id' => $model->id],
    'data-method' => 'post',
]) ?></h3></div>
<div class="ferryman-bank-details">
    <?= GridView::widget([
        'dataProvider' => $dpBankDetails,
        'layout' => '{items}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [

            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', ['/ferrymen-bank-account/update', 'id' => $model->id], [
                            'title' => Yii::t('yii', 'Редактировать'),
                            'class' => 'btn btn-xs btn-default',
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', ['/ferrymen/delete-bank-account', 'id' => $model->id], [
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
