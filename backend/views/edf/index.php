<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\EdfController;
use common\models\EdfStates;

/* @var $this yii\web\View */
/* @var $searchModel common\models\EdfSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */
/* @var $queryString string */
/* @var $isFilterDs bool */

$this->title = EdfController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = EdfController::ROOT_LABEL;
?>
<div class="edf-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

        <?= Html::a('<i class="fa fa-file-excel-o" aria-hidden="true"></i> Экспорт в Excel', EdfController::ROOT_URL_FOR_SORT_PAGING . '?export=true' . $queryString, ['class' => 'btn btn-default pull-right']) ?>

    </p>
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'rowOptions' => function ($model, $key, $index, $grid) {
            /* @var $model \common\models\Edf */

            $options = [];
            switch ($model->state_id) {
                case EdfStates::STATE_ОТКАЗ:
                    $options = ['class' => 'danger'];
                    break;
                case EdfStates::STATE_НА_ПОДПИСИ_У_ЗАКАЗЧИКА:
                case EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА:
                    $options = ['class' => 'info'];
                    break;
                case EdfStates::STATE_СОГЛАСОВАНИЕ:
                    $options = ['class' => 'warning'];
                    break;
                case EdfStates::STATE_ПОДПИСАН_РУКОВОДСТВОМ:
                    $options = ['class' => 'success'];
                    break;
                case EdfStates::STATE_ЗАВЕРШЕНО:
                    $options = ['style' => 'background-color:#eee;'];
                    break;
            }

            return $options;
        },
        'columns' => [
            [
                'attribute' => 'created_at',
                'label' => 'Создан',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'options' => ['width' => '130'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'managerProfileName',
                'format' => 'html',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\Edf */
                    /* @var $column \yii\grid\DataColumn */

                    $newMessages = '';
                    if ($model->unreadMessagesCount > 0)
                        $newMessages = ' <i class="fa fa-commenting text-primary" aria-hidden="true" title="Новых сообщений: ' . $model->unreadMessagesCount . '"></i>';

                    return $model->{$column->attribute} . $newMessages;
                },
                //'visible' => !Yii::$app->user->can('sales_department_manager'),
            ],
            [
                'attribute' => 'stateName',
                'format' => 'html',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\Edf */
                    /* @var $column \yii\grid\DataColumn */

                    if (!empty($model->stateChangedAt)) {
                        return Html::tag('abbr', $model->{$column->attribute}, ['title' => 'Статус приобретен ' . Yii::$app->formatter->asDate($model->stateChangedAt, 'php:d.m.Y в H:i')]);
                    }
                    else {
                        return $model->{$column->attribute};
                    }
                },
            ],
            [
                'attribute' => 'typeName',
                'visible' => !$isFilterDs,
            ],
            [
                'attribute' => 'contractTypeName',
                'visible' => !$isFilterDs,
            ],
            'req_name_short',
            'organizationName',
            'doc_num',
            [
                'attribute' => 'parentRep',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\Edf */
                    /* @var $column \yii\grid\DataColumn */

                    return $model->{$column->attribute};
                },
                'visible' => $isFilterDs,
            ],
            // 'ba_id',
            // 'manager_id',
            //
            // 'doc_date',
            // 'ca_name',
            // 'ca_contact_person',
            // 'ca_basis',
            // 'req_name',
            // 'req_ogrn',
            // 'req_inn',
            // 'req_kpp',
            // 'req_address_j',
            // 'req_address_f',
            // 'req_an',
            // 'req_bik',
            // 'req_bn',
            // 'req_ca',
            // 'req_phone',
            // 'req_email:email',
            // 'req_dir_post',
            // 'req_dir_name',
            // 'is_received_scan',
            // 'is_received_original',
            [
                'class' => 'backend\components\grid\ActionColumn',
                'visibleButtons' => [
                    'delete' => Yii::$app->user->can('root') || Yii::$app->user->can('ecologist_head'),
                ],
            ],
        ],
    ]); ?>

</div>
<?php
$url = \yii\helpers\Url::to(['/edf/']);

$this->registerJs(<<<JS

JS
, \yii\web\View::POS_READY);
?>