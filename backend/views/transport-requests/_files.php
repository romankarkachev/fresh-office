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
                        /** @var $model \common\models\TransportRequestsFiles */
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
                        /** @var $model \common\models\TransportRequestsFiles */
                        /** @var $column \yii\grid\DataColumn */

                        return Html::a(
                            '<i class="fa fa-cloud-download text-info" style="font-size: 18pt;"></i>',
                            ['/transport-requests/download-file', 'id' => $model->id],
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
                            /* @var $model \common\models\TransportRequestsFiles */
                            /* @var $column \yii\grid\DataColumn */

                            return Html::a('<i class="fa fa-trash-o"></i>', ['/transport-requests/delete-file', 'id' => $model->id], ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
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

<?php
$url = Url::to(['/transport-requests/preview-file']);

$this->registerJs(<<<JS
$("#new_files").on("filebatchuploadsuccess", function(event, data, previewId, index) {
    $.pjax.reload({container:"#afs"});
});

// Обработчик щелчка по ссылкам в колонке "Наименование" в таблице файлов.
//
function previewFileOnClick() {
    id = $(this).attr("data-id");
    if (id != "") {
        $("#modal_title").text("Предпросмотр файла");
        $("#modal_body").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#mw_summary").modal();
        $("#modal_body").load("$url?id=" + id);
    }

    return false;
} // previewFileOnClick()

$(document).on("click", "a[id ^= 'previewFile']", previewFileOnClick);
JS
    , \yii\web\View::POS_READY);
?>
