<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\TransportRequestsStates;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TransportRequestsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = 'Запросы на транспорт | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Запросы на транспорт';
?>
<div class="transport-requests-list">
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            'customer_name',
            [
                'attribute' => 'stateName',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\TransportRequests */
                    if ($model->state_id == TransportRequestsStates::STATE_ЗАКРЫТ)
                        return '<i class="fa fa-check-square-o text-success" aria-hidden="true"></i>';

                    if ($model->state_id == TransportRequestsStates::STATE_НОВЫЙ)
                        return '<strong>' . $model->{$column->attribute} . '</strong>';

                    return $model->{$column->attribute};
                },
                'options' => ['width' => '100'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Создан',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\TransportRequests */
                    return Yii::$app->formatter->asDate($model->created_at, 'php:d.m.Y в H:i');
                },
                'options' => ['width' => '150'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            'createdByName',
            [
                'attribute' => 'regionName',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\TransportRequests */

                    return $model->regionName . ' ' . $model->cityName;
                },
            ],
            'periodicityName',
            //'finished_at',
            //'customer_id',
            // 'city_id',
            // 'address',
            // 'comment_manager:ntext',
            // 'comment_logist:ntext',
            // 'our_loading',
            //
            // 'special_conditions:ntext',
            // 'spec_free',
            // 'spec_hose',
            // 'spec_cond:ntext',
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-xs btn-default']);
                    },
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
<?php
$this->registerJs(<<<JS
// Функция-обработчик изменения даты в любом из соответствующих полей.
//
function anyDateOnChange() {
    //\$button = $("button[type='submit']");
    \$button = $("#btnSearch");
    \$button.attr("disabled", "disabled");
    text = \$button.text();
    \$button.text("Подождите...");
    setTimeout(function () {
        \$button.removeAttr("disabled");
        \$button.text(text);
    }, 1500);
}
JS
, \yii\web\View::POS_BEGIN);
?>
