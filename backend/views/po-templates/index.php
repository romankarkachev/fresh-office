<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\components\grid\TotalAmountColumn;
use backend\controllers\PoTemplatesController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PoAtSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = PoTemplatesController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = PoTemplatesController::ROOT_LABEL;
?>
<div class="po-at-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'layout' => "{items}\n<small class=\"pull-right form-text text-muted\">{summary}</small>\n{pager}",
        'dataProvider' => $dataProvider,
        'columns' => [
            'companyName',
            [
                'attribute' => 'eiRepHtml',
                'format' => 'raw',
            ],
            [
                'class' => TotalAmountColumn::class,
                'attribute' => 'amount',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\Po */
                    /* @var $column \yii\grid\DataColumn */

                    return \common\models\FinanceTransactions::getPrettyAmount($model->{$column->attribute}, 'html');
                },
                'options' => ['width' => '100'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-right'],
            ],
            'comment:ntext',
            [
                'attribute' => 'periodicity',
                'options' => ['width' => '30'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'is_active',
                'label' => 'Состояние',
                'format' => 'html',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\PoAt */
                    /* @var $column \yii\grid\DataColumn */

                    if (!empty($model->{$column->attribute}))
                        return '<i class="fa fa-check-circle text-success" aria-hidden="true" title="Автоплатеж активен, применяется"></i>';
                    else
                        return '<small class="text-muted"><em>выключен</em></small>';
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '30'],

            ],
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
