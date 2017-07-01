<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>

<div class="files-list">
    <div class="table-responsive">
        <?php Pjax::begin(['id' => 'afs']); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'id' => 'gw-files',
            'layout' => '{items}',
            'tableOptions' => ['class' => 'table table-striped table-hover'],
            'columns' => [
                [
                    'attribute' => 'ofn',
                    'label' => 'Имя файла',
                    'contentOptions' => ['style' => 'vertical-align: middle;'],
                ],
                [
                    'label' => 'Скачать',
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Html::a('<i class="fa fa-cloud-download text-info" style="font-size: 18pt;"></i>', ['/ferrymen-drivers/download-file', 'id' => $data->id], ['title' => ($data->ofn != ''?$data->ofn.', ':'').Yii::$app->formatter->asShortSize($data->size, false), 'target' => '_blank', 'data-pjax' => 0]);
                    },
                    'options' => ['width' => '60'],
                ],
                [
                    'attribute' => 'uploaded_at',
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
                    'format' =>  ['date', 'dd.MM.Y HH:mm'],
                    'options' => ['width' => '130']
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => 'Действия',
                    'template' => '{delete}',
                    'buttons' => [
                        'delete' => function ($url, $model) {
                            return Html::a('<i class="fa fa-trash-o"></i>', ['/ferrymen-drivers/delete-file', 'id' => $model->id], ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                        }
                    ],
                    'options' => ['width' => '40'],
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-right', 'style' => 'vertical-align: middle;'],
                ],
            ],
        ]); ?>

        <?php Pjax::end(); ?>

    </div>
</div>