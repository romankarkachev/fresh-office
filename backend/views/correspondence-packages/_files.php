<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>

<div class="files-list">
    <div class="table-responsive">
        <?php Pjax::begin(['id' => 'pjax-files', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

        <?= GridView::widget([
            'id' => 'gw-files',
            'dataProvider' => $dataProvider,
            'showOnEmpty' => false,
            'emptyText' => '<div class="well well-small">Файлы к пакету корреспонденции не прикреплялись.</div>',
            'layout' => '{items}',
            'tableOptions' => ['class' => 'table table-striped table-hover'],
            'columns' => [
                [
                    'attribute' => 'ofn',
                    'label' => 'Имя файла',
                    'contentOptions' => ['style' => 'vertical-align: middle;'],
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                        /** @var $model \common\models\CorrespondencePackagesFiles */
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
                        /** @var $model \common\models\CorrespondencePackagesFiles */
                        /** @var $column \yii\grid\DataColumn */

                        return Html::a(
                            '<i class="fa fa-cloud-download text-info" style="font-size: 18pt;"></i>',
                            ['/correspondence-packages/download-file', 'id' => $model->id],
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
                            /* @var $model \common\models\CorrespondencePackagesFiles */

                            // только так не скроллится наверх (то есть при помощи заключения в форму):
                            return Html::beginForm(['/correspondence-packages/delete-file', 'id' => $model->id], 'post', ['data-pjax' => true]) .
                                Html::a(
                                    '<i class="fa fa-trash-o"></i>',
                                    ['/correspondence-packages/delete-file', 'id' => $model->id],
                                    [
                                        'title' => Yii::t('yii', 'Удалить'),
                                        'class' => 'btn btn-xs btn-danger',
                                        'aria-label' => Yii::t('yii', 'Delete'),
                                        'data-confirm' => 'Будет выполнено физическое удаление файла из данного пакета корреспонденции. Операция необратима. Продолжить?',
                                        'data-method' => 'post',
                                        'data-pjax' => '0',
                                    ]
                                ) . Html::endForm();
                        }
                    ],
                    'options' => ['width' => '20'],
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center', 'style' => 'vertical-align: middle;'],
                ],
            ],
        ]); ?>

        <?php Pjax::end(); ?>

    </div>
</div>
<div id="mw_preview" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="modal_title" class="modal-title">Предпросмотр файла</h4>
            </div>
            <div id="modal_body_preview" class="modal-body">
                <p>One fine body…</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<?php
$url = Url::to(['/correspondence-packages/preview-file']);

$this->registerJs(<<<JS
$("#new_files").on("filebatchuploadsuccess", function(event, data, previewId, index) {
    $.pjax.reload({container:"#pjax-files"});
});

// Обработчик щелчка по ссылкам в колонке "Наименование" в таблице файлов.
//
function previewFileOnClick() {
    id = $(this).attr("data-id");
    if (id != "") {
        $("#modal_body_preview").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#mw_preview").modal();
        $("#modal_body_preview").load("$url?id=" + id);
    }

    return false;
} // previewFileOnClick()

$(document).on("click", "a[id ^= 'previewFile']", previewFileOnClick);
JS
, \yii\web\View::POS_READY);
?>
