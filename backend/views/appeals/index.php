<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use common\models\Appeals;

/* @var $this yii\web\View */
/* @var $searchModel common\models\AppealsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = 'Обращения | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Обращения';
?>
<div class="appeals-list">
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <p>
        <?php if (Yii::$app->user->can('root')): ?>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?php endif; ?>
        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

        <?= Html::a('<i class="fa fa-magic" aria-hidden="true"></i> Мастер обработки обращений', ['/appeals/wizard'], ['class' => 'btn btn-default']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'rowOptions' => function ($model, $key, $index, $grid) {
            /* @var $model \common\models\Appeals */
            switch ($model->state_id) {
                case Appeals::APPEAL_STATE_CLOSED:
                    return ['class' => 'text-muted'];
                    break;
                case Appeals::APPEAL_STATE_REJECT:
                    return ['class' => 'danger'];
                    break;
                case Appeals::APPEAL_STATE_SUCCESS:
                    return ['class' => 'success'];
                    break;
            }
        },
        'columns' => [
            [
                'attribute' => 'id',
                'options' => ['width' => '30'],
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Создано',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            [
                'attribute' => 'createdByProfileName',
                'visible' => Yii::$app->user->can('root'),
            ],
            'fo_company_name',
            [
                'attribute' => 'appealSourceName',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '200'],
            ],
            [
                'attribute' => 'caStateName',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '120'],
            ],
            [
                'attribute' => 'appealStateName',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '170'],
            ],
            [
                'header' => 'Способ',
                'format' => 'raw',
                'value' => function ($model) {
                    /* @var $model \common\models\Appeals */
                    if ($model->request_referrer == null)
                        return '<i class="fa fa-pencil-square-o" aria-hidden="true"></i>';
                    else
                        return '<i class="fa fa-globe" aria-hidden="true"></i>';
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '60'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'visible' => Yii::$app->user->can('root'),
                'header' => 'Действия',
                'template' => '{update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i> изменить', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-xs btn-default']);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', $url, ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                    }
                ],
                'options' => ['width' => '120'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>
</div>
