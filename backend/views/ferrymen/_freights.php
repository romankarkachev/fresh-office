<?php

use backend\components\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $totalAmount float общая сумма по всем рейсам (вне зависимости от номера просматриваемой страницы) */
?>

<div class="panel panel-info">
    <div class="panel-body table-responsive">
        <?php Pjax::begin(['id' => 'ff', 'enablePushState' => false]); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'id' => 'gw-ff',
            'layout' => '{pager}{items}<div style="float:left;">{summary}</div><div style="float:right;">Общая сумма: <strong>' . Yii::$app->formatter->asCurrency($totalAmount) . '</strong></div>',
            'tableOptions' => ['class' => 'table table-striped table-hover'],
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
                'dannie',
                [
                    'attribute' => 'cost',
                    'label' => 'Стоимость',
                    'format' => 'currency',
                    'options' => ['width' => '120'],
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
            ],
        ]); ?>

        <?php Pjax::end(); ?>

    </div>
</div>