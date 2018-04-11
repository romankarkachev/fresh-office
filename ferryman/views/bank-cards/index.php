<?php

use ferryman\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\FerrymenBankCardsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Банковские карты | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Банковские карты';

$this->params['breadcrumbsRight'][] = ['label' => 'Создать', 'icon' => 'fa fa-plus-circle fa-lg', 'url' => ['create'], 'class' => 'btn text-success'];
$this->params['breadcrumbsRight'][] = ['icon' => 'fa fa-sort-amount-asc', 'url' => ['/bank-cards'], 'title' => 'Сбросить отбор и применить сортировку по-умолчанию'];
?>
<div class="bank-cards-list">
    <div class="card">
        <div class="card-block">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    'number',
                    'cardholder',
                    'bank',
                    ['class' => 'ferryman\components\grid\ActionColumn'],
                ],
            ]); ?>

        </div>
    </div>
</div>
