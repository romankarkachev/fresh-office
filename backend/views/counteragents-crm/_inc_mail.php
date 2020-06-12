<?php

use yii\widgets\Pjax;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\IncomingMailSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div class="company-incoming-mail">
    <?php Pjax::begin(['id' => 'pjax-inc-mail', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'created_at',
                'label' => 'Создан',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            'createdByProfileName:ntext:Автор',
            'inc_num:ntext:Вх. №',
            [
                'attribute' => 'inc_date',
                'label' => 'Получен',
                'format' => ['datetime', 'dd.MM.YYYY'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '90'],
            ],
            'typeName:ntext:Тип письма',
            [
                'attribute' => 'organizationName',
                'label' => 'Получатель',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\IncomingMail */
                    /* @var $column \yii\grid\DataColumn */

                    $receiverProfileName = '';
                    if (!empty($model->receiverProfileName)) {
                        $receiverProfileName = '<br />' . $model->receiverProfileName;
                    }

                    return $model->{$column->attribute} . $receiverProfileName;
                },
            ],
            'description:ntext',
            //'date_complete_before',
            //'ca_src',
            //'ca_id',
            //'receiver_id',
            'comment:ntext',
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
