<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use common\models\LicensesRequestsStates;

/* @var $this yii\web\View */
/* @var $searchModel common\models\LicensesRequestsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = 'Запросы лицензий | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Запросы лицензий';

$actionButtonsTemplate = '{copy}';
if (Yii::$app->user->can('root')) {
    $actionButtonsTemplate = '{copy} {update} {delete}';
}
elseif (Yii::$app->user->can('sales_department_head')) {
    $actionButtonsTemplate = '{copy} {update}';
}
?>
<div class="licenses-requests-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

    </p>
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'attribute' => 'created_at',
                'label' => 'Создан',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\LicensesRequests */

                    return Yii::$app->formatter->asDate($model->{$column->attribute}, 'php:d.m.Y в H:i');
                },
                'options' => ['width' => '150'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            'createdByName',
            [
                'attribute' => 'fkkos',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\LicensesRequests */

                    return nl2br($model->{$column->attribute});
                },
            ],
            'ca_email:email',
            'ca_name',
            'organizationName',
            [
                'header' => 'Статус',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\LicensesRequests */

                    switch ($model->state_id) {
                        case LicensesRequestsStates::LICENSE_STATE_НОВЫЙ:
                            return '<i class="fa fa-asterisk text-warning" aria-hidden="true" title="Новый необработанный запрос"></i>';
                        case LicensesRequestsStates::LICENSE_STATE_ОДОБРЕН:
                            return '<i class="fa fa-check-circle-o text-success" aria-hidden="true" title="Запрос рассмотрен и одобрен"></i>';
                        case LicensesRequestsStates::LICENSE_STATE_ОТКАЗ:
                            return '<i class="fa fa-times-circle text-danger" aria-hidden="true" title="Запрос отклонен администратором"></i>';
                        default:
                            return '';
                    }
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '60'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => $actionButtonsTemplate,
                'buttons' => [
                    'copy' => function ($url, $model) {
                        return Html::a('<i class="fa fa-copy"></i>', $url, ['title' => Yii::t('yii', 'Копировать'), 'class' => 'btn btn-xs btn-default text-primary']);
                    },
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-xs btn-default']);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', $url, ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                    }
                ],
                'options' => ['width' => '96'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'visible' => Yii::$app->user->can('root') || Yii::$app->user->can('sales_department_head') || Yii::$app->user->can('sales_department_manager'),
            ],
        ],
    ]); ?>

</div>
