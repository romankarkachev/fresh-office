<?php

use common\models\YandexSpeechKitRecognitionQueue;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\YandexSpeechKitRecognitionQueueSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = YandexSpeechKitRecognitionQueue::PAGE_TITLE . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = YandexSpeechKitRecognitionQueue::BREADCRUMBS_TITLE;
?>
<div class="yandex-speech-kit-recognition-queue">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'check_after',
                'label' => 'Проверять с',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Поставлено',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
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
        ],
    ]); ?>

</div>