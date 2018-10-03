<?php

use yii\helpers\Url;
use ferryman\components\grid\GridView;
use yii\web\JsExpression;
use dosamigos\switchery\Switchery;

/* @var $this yii\web\View */
/* @var $searchModel common\models\CEMailboxesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Почтовые ящики | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Почтовые ящики';

$this->params['breadcrumbsRight'][] = ['label' => 'Создать', 'icon' => 'fa fa-plus-circle fa-lg', 'url' => ['create'], 'class' => 'btn text-success'];
$this->params['breadcrumbsRight'][] = ['icon' => 'fa fa-sort-amount-asc', 'url' => ['/mailboxes'], 'title' => 'Сбросить отбор и применить сортировку по-умолчанию'];

$urlToggleActivity = Url::to(['/mailboxes/toggle-activity']);
?>
<div class="cemailboxes-list">
    <div class="card">
        <div class="card-block">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'name',
                        'format' => 'raw',
                        'value' => function($model, $key, $index, $column) {
                            /* @var $model \common\models\CEMailboxes */
                            /* @var $column \yii\grid\DataColumn */

                            return $model->{$column->attribute} . ($model->is_primary_done === -1 ? ' <i class="fa fa-exclamation-triangle text-danger" aria-hidden="true" title="Первичный сбор писем по этому ящику провалился с треском"></i>' : '');
                        }
                    ],
                    'typeName',
                    'categoryName',
                    [
                        'attribute' => 'is_active',
                        'label' => 'Активен',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $column) use ($urlToggleActivity) {
                            /** @var \common\models\CEMailboxes $model */
                            /** @var \yii\grid\DataColumn $column */

                            return Switchery::widget([
                                'id' => 'toggleActive' . $model->id,
                                'name' => $column->attribute,
                                'checked' => $model->is_active,
                                'options' => ['data-id' => $model->id],
                                'clientOptions' => ['size' => 'small'],
                                'clientEvents' => [
                                    'change' => new JsExpression('function() {
    $.post("' . $urlToggleActivity . '", {id: $(this).attr("data-id")});
}')
                                ]
                            ]);
                        },
                        'options' => ['width' => '80'],
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    //'messagesCount',
                    ['class' => 'ferryman\components\grid\ActionColumn'],
                ],
            ]); ?>

        </div>
    </div>
</div>
