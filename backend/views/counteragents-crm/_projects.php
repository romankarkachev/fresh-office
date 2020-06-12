<?php

use yii\widgets\Pjax;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
?>
<div class="company-projects">
    <?php Pjax::begin(['id' => 'pjax-projects', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped'],
        'columns' => [
            [
                'attribute' => 'DATE_CREATE_PROGECT',
                'label' => 'Создан',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            'ID_LIST_PROJECT_COMPANY',
            'typeName',
            [
                'attribute' => 'ADD_vivozdate',
                'format' =>  ['date', 'dd.MM.YYYY'],
            ],
            'stateName',
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
