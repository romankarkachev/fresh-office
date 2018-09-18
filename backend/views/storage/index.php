<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use common\models\TransportRequests;

/* @var $this yii\web\View */
/* @var $searchModel common\models\FileStorageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $foreignRecord bool признак, определяющий наличие доступа к файлам выбранного контрагента */

$this->title = 'Файловое хранилище | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Файловое хранилище';
?>
<div class="file-storage-list">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <?php if (!Yii::$app->user->can('sales_department_head')): ?>
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Добавить файл в хранилище', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?php endif; ?>
    <?php
    if ($searchModel->ca_id == null) echo $this->render('_filter_required');
    elseif ($foreignRecord) echo $this->render('_forbidden_foreign', [
        'details' => [
            'breadcrumbs' => ['label' => 'Хранилище', 'url' => ['/storage']],
            'modelRep' => TransportRequests::getCustomerName($searchModel->ca_id),
            'buttonCaption' => 'Хранилище',
            'buttonUrl' => ['/storage'],
        ],
    ]);
    else
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'attribute' => 'uploaded_at',
                'label' => 'Создан',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            'uploadedByProfileName',
            'typeName',
            'ca_name',
            [
                'attribute' => 'ofn',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\FileStorage */
                    /* @var $column \yii\grid\DataColumn */

                    if (Yii::$app->user->can('root'))
                        return Html::a($model->ofn, ['download', 'id' => $model->id]);
                    else
                        return $model->ofn;
                }
            ],
            'size:shortSize',
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{preview} {update} {delete}',
                'buttons' => [
                    'preview' => function ($url, $model) {
                        return Html::a('<i class="fa fa-eye"></i>', $url, ['title' => Yii::t('yii', 'Просмотр содержимого файла'), 'class' => 'btn btn-xs btn-default']);
                    },
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-xs btn-default']);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', $url, ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                    }
                ],
                'visibleButtons' => [
                    'update' => Yii::$app->user->can('root'),
                    'delete' => Yii::$app->user->can('root'),
                ],
                'options' => ['width' => '100'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]);
    ?>

</div>
