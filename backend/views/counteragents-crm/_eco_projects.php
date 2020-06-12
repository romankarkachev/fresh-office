<?php

use yii\widgets\Pjax;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
?>
<div class="company-eco-projects">
    <?php Pjax::begin(['id' => 'pjax-eco-projects', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped'],
        'columns' => [
            [
                'attribute' => 'created_at',
                'label' => 'Создан',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            [
                'attribute' => 'date_start',
                'label' => 'Запущен',
                'format' => ['date', 'dd.MM.YYYY'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            [
                'attribute' => 'date_close_plan',
                'label' => 'Срок',
                'format' => ['date', 'dd.MM.YYYY'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            // можно и организации, но они нигде не заполняются
            //'organizationName:ntext:Организация',
            'typeName:ntext:Тип',
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
