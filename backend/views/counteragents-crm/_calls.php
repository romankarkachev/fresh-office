<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use backend\components\grid\GridView;
use common\models\pbxCalls;
use \backend\controllers\PbxCallsController;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
?>
<div class="company-calls">
    <?php Pjax::begin(['id' => 'pjax-calls', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped'],
        'columns' => [
            [
                'attribute' => 'calldate',
                'label' => 'Дата',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            [
                'label' => 'Статус',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\pbxCalls */
                    /* @var $column \yii\grid\DataColumn */

                    return $model->stateName;
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => function ($model, $key, $index, $gridView) {
                    /* @var $model \common\models\pbxCalls */

                    return ['class' => 'text-center' . $model->stateElementClass];
                },
                'options' => ['width' => '110'],
            ],
            [
                'attribute' => 'src',
                'label' => 'Абонент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\pbxCalls */
                    /* @var $column \yii\grid\DataColumn */

                    $result = $model->{$column->attribute};

                    if (strlen($result) > 1 && strlen($result) <= 4) {
                        $employeeName = $model->srcEmployeeName;
                        if (!empty($employeeName)) {
                            $result .= ' <small class="text-muted">' . $employeeName . '</small>';
                        }
                    }

                    return $result;
                },
            ],
            [
                'attribute' => 'dst',
                'label' => 'Адресат',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\pbxCalls */
                    /* @var $column \yii\grid\DataColumn */

                    $result = $model->{$column->attribute};
                    $employeeName = $model->dstEmployeeName;
                    if (!empty($employeeName)) {
                        $result .= ' <small class="text-muted">' . $employeeName . '</small>';
                    }

                    return $result;
                },
            ],
            [
                'attribute' => 'billsec',
                'label' => 'Длительность',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\pbxCalls */
                    /* @var $column \yii\grid\DataColumn */

                    $duration = pbxCalls::formatConversationDuration($model->{$column->attribute});
                    if ($duration != '-') {
                        $buttons = Html::a(
                            Html::tag('i', '', ['class' => 'fa fa-play-circle-o text-primary', 'aria-hidden' => 'true']) . ' ' . $duration,
                            Url::to(['/pbx-calls/preview-file', 'id' => $model->id]),
                            ['id' => 'btnPlay' . $model->id, 'class' => 'btn btn-default btn-xs', 'title' => 'Воспроизвести эту запись разговора', 'data-pjax' => '0']);

                        // кнопка "Скачать результаты распознавания разговора"
                        if (!empty($model->recognitionFfp)) {
                            $buttons .= ' ' . Html::a(Html::tag('i', '', ['class' => 'fa fa-cloud-download', 'aria-hidden' => true]), Url::to(\yii\helpers\ArrayHelper::merge(PbxCallsController::URL_DOWNLOAD_RECOGNITION_RESULT_AS_ARRAY, ['id' => $model->id])), ['class' => 'btn btn-warning btn-xs', 'title' => 'Скачать результаты распознавания разговора', 'data-pjax' => '0']);
                        }

                        return $buttons;
                    }
                    else
                        return $duration;
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '110'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
<div id="mvPhoneConversation" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div id="modal_container" class="modal-dialog modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Запись разговора</h4>
            </div>
            <div class="modal-body">
                <?= $this->render('../pbx-calls/_conversation_play_form'); ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
