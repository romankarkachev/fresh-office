<?php

use yii\helpers\Html;
use common\models\YandexSpeechKitRecognitionQueue;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\YandexSpeechKitRecognitionQueueSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = YandexSpeechKitRecognitionQueue::PAGE_TITLE . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = YandexSpeechKitRecognitionQueue::BREADCRUMBS_TITLE;
?>
<div class="form-group">
    <?= Html::a('<i class="fa fa-trash-o"></i> Очистить очередь', \backend\controllers\PbxCallsController::URL_CLEAR_RECOGNITION_QUEUE, ['title' => 'Очистить очередь распознавания (файлы остаются в бакете)', 'class' => 'btn btn-danger pull-right', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => 'Вы действительно хотите очистить очередь распознавания?', 'data-method' => 'post']) ?>

</div>
<br />
<div class="yandex-speech-kit-recognition-queue">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'check_after',
                'label' => 'Проверять с',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm:ss'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '140'],
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Поставлено',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm:ss'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '140'],
            ],
            [
                'attribute' => 'createdByProfileName',
                'label' => 'Отправитель',
                'options' => ['width' => '200'],
            ],
            [
                'attribute' => 'call_id',
                'options' => ['width' => '90'],
            ],
            'url_bucket:url:Ссылка в бакете',
            [
                'attribute' => 'operation_id',
                'label' => 'ID операции',
                'options' => ['width' => '200'],
            ],
            [
                'class' => 'backend\components\grid\ActionColumn',
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', \backend\controllers\PbxCallsController::URL_DELETE_FROM_RECOGNITION_QUEUE, ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post']);
                    }
                ],
                'visibleButtons' => [
                    'delete' => Yii::$app->user->can('root'),
                ],
                'options' => ['width' => '30'],
            ],
        ],
    ]); ?>

</div>
