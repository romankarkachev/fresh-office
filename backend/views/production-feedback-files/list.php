<?php

use yii\helpers\Html;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ProductionFeedbackFilesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Обратная связь от производства | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Обратная связь от производства';
?>
<div class="production-feedback-files-list">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'attribute' => 'uploaded_at',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
                'format' =>  ['date', 'dd.MM.Y HH:mm'],
                'options' => ['width' => '130']
            ],
            'uploadedByName',
            'ofn',
            [
                'attribute' => 'action',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\ProductionFeedbackFiles */

                    if ($model->action == 1)
                        return '<i class="fa fa-times-circle text-warning" aria-hidden="true"></i>';
                    else
                        return '<i class="fa fa-check-circle text-success" aria-hidden="true"></i>';
                },
            ],
            'project_id',
            'size:shortSize',
            [
                'label' => 'Скачать',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
                'format' => 'raw',
                'value' => function ($data) {
                    return Html::a('<i class="fa fa-cloud-download text-info" style="font-size: 18pt;"></i>', ['/production-feedback-files/download-file', 'id' => $data->id], ['title' => ($data->ofn != ''?$data->ofn.', ':'').Yii::$app->formatter->asShortSize($data->size, false), 'target' => '_blank', 'data-pjax' => 0]);
                },
                'options' => ['width' => '60'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', $url, ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                    }
                ],
                'options' => ['width' => '80'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>