<?php

use ferryman\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\CEMailboxesCategoriesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Категории почтовых ящиков | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Категории';

$this->params['breadcrumbsRight'][] = ['label' => 'Создать', 'icon' => 'fa fa-plus-circle fa-lg', 'url' => ['create'], 'class' => 'btn text-success'];
$this->params['breadcrumbsRight'][] = ['icon' => 'fa fa-sort-amount-asc', 'url' => ['/categories'], 'title' => 'Сбросить отбор и применить сортировку по-умолчанию'];
?>
<div class="cemailboxes-categories-list">
    <div class="card">
        <div class="card-block">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    'name',
                    ['class' => 'ferryman\components\grid\ActionColumn'],
                ],
            ]); ?>

        </div>
    </div>
</div>
