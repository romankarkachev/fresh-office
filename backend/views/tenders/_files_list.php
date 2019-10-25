<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use common\models\TendersFiles;
use backend\controllers\TendersController;

/* @var $this yii\web\View */
/* @var $tender \common\models\Tenders */
/* @var $searchModel \common\models\TendersFilesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider of common\models\TendersFiles */

$btnDownloadFewPrompt = TendersFiles::DOM_IDS['BUTTON_DOWNLOAD_SELECTED_PROMPT'];
$gridViewId = TendersFiles::DOM_IDS['GRIDVIEW_ID'];
?>

<?php Pjax::begin(['id' => 'pjax-files', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

<?= $this->render('_search_files', ['model' => $searchModel]); ?>

<?= \backend\components\grid\GridView::widget([
    'id' => $gridViewId,
    'dataProvider' => $dataProvider,
    'showOnEmpty' => false,
    'emptyText' => '<div class="well well-small">Файлы отсутствуют.</div>',
    'layout' => '{items}',
    'tableOptions' => ['class' => 'table table-striped table-hover'],
    'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn',
            'options' => ['width' => '30'],
        ],
        [
            'label' => 'Скачать',
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
            'format' => 'raw',
            'value' => function ($model, $key, $index, $column) {
                /** @var $model \common\models\TendersFiles */
                /** @var $column \yii\grid\DataColumn */

                return Html::a(
                    '<i class="fa fa-cloud-download text-info" style="font-size: 18pt;"></i>',
                    ['/' . TendersController::ROOT_URL_FOR_SORT_PAGING . '/' . TendersController::URL_DOWNLOAD_FILE, 'id' => $model->id],
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
            'attribute' => 'ofn',
            'label' => 'Имя файла',
            'contentOptions' => ['style' => 'vertical-align: middle;'],
            'format' => 'raw',
            'value' => function ($model, $key, $index, $column) {
                /** @var $model \common\models\TendersFiles */
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
            'attribute' => 'ctName',
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center'],
            'options' => ['width' => '200'],
            'visible' => empty($searchModel->ct_id),
        ],
        [
            'attribute' => 'revision',
            'label' => 'Ред.',
            'headerOptions' => ['class' => 'text-center'],
            'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
            'options' => ['width' => '70'],
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
                    return Html::beginForm(['/' . TendersController::ROOT_URL_FOR_SORT_PAGING . '/' . TendersController::URL_DELETE_FILE, 'id' => $model->id], 'post', ['data-pjax' => true]) .
                        Html::a(
                            '<i class="fa fa-times"></i>',
                            ['/' . TendersController::ROOT_URL_FOR_SORT_PAGING . '/' . TendersController::URL_DELETE_FILE, 'id' => $model->id],
                            [
                                'title' => Yii::t('yii', 'Удалить'),
                                'class' => 'btn btn-xs btn-danger',
                                'aria-label' => Yii::t('yii', 'Delete'),
                                'data-confirm' => 'Будет выполнено физическое удаление файла из данного тендера. Операция необратима. Продолжить?',
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

<?php if ($dataProvider->getTotalCount() > 0): ?>
<div class="form-group">
    <?= Html::a($btnDownloadFewPrompt, '#', ['id' => 'downloadSelectedFiles', 'class' => 'btn btn-default btn-xs', 'title' => 'Скачать выделенные файлы одним архивом', 'data-pjax' => '0']) ?>

</div>
<?php endif; ?>
<?php Pjax::end(); ?>

<div id="mw_preview" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header"><h4 id="modal_title" class="modal-title">Предпросмотр файла</h4></div>
            <div id="modal_body_preview" class="modal-body"><p>One fine body…</p></div>
            <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button></div>
        </div>
    </div>
</div>
