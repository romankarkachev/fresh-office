<?php

use ferryman\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\foProjectsSearch */
/* @var $dataProvider yii\data\ArrayDataProvider */

$this->title = 'Заказы | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Заказы';
?>
<div class="orders-list">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <div class="card">
        <div class="card-block">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'id',
                        'label' => '№ заказа',
                        'options' => ['width' => '120'],
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'vivozdate',
                        'format' => 'date',
                        'options' => ['width' => '120'],
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    'type_name',
                    'state_name',
                ],
            ]); ?>

        </div>
    </div>
</div>
