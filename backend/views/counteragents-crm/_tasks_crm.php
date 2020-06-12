<?php

use yii\widgets\Pjax;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */
?>
<div class="company-tasks-crm">
    <?php Pjax::begin(['id' => 'pjax-tasks-crm', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped'],
        'columns' => [
            'created_at:date:Дата',
            [
                'attribute' => 'contactPersonName',
                'label' => 'Контактное лицо',
                'options' => ['width' => '230'],
            ],
            'description:ntext:Задание',
            'comment:ntext:Ответ',
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
