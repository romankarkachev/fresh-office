<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>

<div class="panel panel-info">
    <div class="panel-body table-responsive">
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
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                        /** @var $model \common\models\Ferrymen */
                        /** @var $column \yii\grid\DataColumn */
                        return Html::a($model->{$column->attribute}, '#', [
                            'class' => 'link-ajax',
                            'id' => 'previewFile-' . $model->id,
                            'data-id' => $model->id,
                            'title' => 'Предварительный просмотр',
                            'data-pjax' => 0,
                        ]);
                    },
                ],
                [
                    'label' => 'Скачать',
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
                    'format' => 'raw',
                    'value' => function ($data) {
                        return Html::a('<i class="fa fa-cloud-download text-info" style="font-size: 18pt;"></i>', ['/ferrymen/download-file', 'id' => $data->id], ['title' => ($data->ofn != ''?$data->ofn.', ':'').Yii::$app->formatter->asShortSize($data->size, false), 'target' => '_blank', 'data-pjax' => 0]);
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
                            return Html::a('<i class="fa fa-trash-o"></i>', ['/ferrymen/delete-file', 'id' => $model->id], ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
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