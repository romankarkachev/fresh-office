<?php

use yii\widgets\Pjax;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
?>
<div class="company-tasks">
    <?php Pjax::begin(['id' => 'pjax-tasks', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped'],
        'columns' => [
            [
                'attribute' => 'created_at',
                'label' => 'Создана',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            [
                'attribute' => 'createdByProfileName',
                'label' => 'Автор',
            ],
            'responsibleProfileName',
            'typeName',
            'stateName',
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
