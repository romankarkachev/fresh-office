<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use common\models\ProductionShipment;
use common\models\ProductionShipmentFiles;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider of common\models\ProductionShipmentFiles */

$pjaxId = ProductionShipmentFiles::DOM_IDS['PJAX_ID'];
$gridViewId = ProductionShipmentFiles::DOM_IDS['GRIDVIEW_ID'];
?>

<?php Pjax::begin(['id' => $pjaxId, 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

<?= \backend\components\grid\GridView::widget([
    'id' => $gridViewId,
    'dataProvider' => $dataProvider,
    'showOnEmpty' => false,
    'emptyText' => '<div class="well well-small">Файлы отсутствуют.</div>',
    'layout' => '{items}',
    'tableOptions' => ['class' => 'table table-striped table-hover'],
    'columns' => [
        [
            'attribute' => 'ofn',
            'label' => 'Имя файла',
            'contentOptions' => ['style' => 'vertical-align: middle;'],
            'format' => 'raw',
            'value' => function ($model, $key, $index, $column) {
                /** @var $model \common\models\ProductionShipmentFiles */
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
            'value' => function ($model, $key, $index, $column) {
                /** @var $model \common\models\ProductionShipmentFiles */
                /** @var $column \yii\grid\DataColumn */

                return Html::a(
                    '<i class="fa fa-cloud-download text-info" style="font-size: 18pt;"></i>',
                    ['/' . ProductionShipment::URL_ROOT . '/' . ProductionShipment::URL_DOWNLOAD_FILE, 'id' => $model->id],
                    [
                        'title' => ($model->ofn != '' ? $model->ofn . ', ' : '') . Yii::$app->formatter->asShortSize($model->size, false),
                        'target' => '_blank',
                        'data-pjax' => 0
                    ]
                );
            },
            'options' => ['width' => '60'],
        ],
        [
            'attribute' => 'uploaded_at',
            'label' => 'Загружен',
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
            'format' =>  ['date', 'dd.MM.Y HH:mm'],
            'options' => ['width' => '130']
        ],
        [
            'class' => 'backend\components\grid\ActionColumn',
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model) {
                    // только так не скроллится наверх (то есть при помощи заключения в форму):
                    return Html::beginForm(['/' . ProductionShipment::URL_ROOT . '/' . ProductionShipment::URL_DELETE_FILE, 'id' => $model->id], 'post', ['data-pjax' => true]) .
                        Html::a(
                            '<i class="fa fa-times"></i>',
                            ['/' . ProductionShipment::URL_ROOT . '/' . ProductionShipment::URL_DELETE_FILE, 'id' => $model->id],
                            [
                                'title' => Yii::t('yii', 'Удалить'),
                                'class' => 'btn btn-xs btn-danger',
                                'aria-label' => Yii::t('yii', 'Delete'),
                                'data-confirm' => 'Будет выполнено физическое удаление файла. Операция необратима. Продолжить?',
                                'data-method' => 'post',
                                'data-pjax' => '0',
                            ]
                        ) . Html::endForm();
                }
            ],
            'options' => ['width' => '20'],
        ],
    ],
]); ?>

<?php Pjax::end(); ?>
