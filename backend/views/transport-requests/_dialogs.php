<?php

use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model common\models\TransportRequestsDialogs */

$current_user_id = Yii::$app->user->id;
?>

<div class="transport-requests-dialogs">
    <div class="table-responsive">
        <?php Pjax::begin(['id' => 'pjax-dialogs', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

        <?= $this->render('_dialog_form', [
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'id' => 'gw-dialogs',
            'layout' => '{items}',
            'tableOptions' => ['class' => 'table table-striped table-hover'],
            'columns' => [
                [
                    'attribute' => 'created_at',
                    'label' => 'Создан',
                    'value' => function($model, $key, $index, $column) {
                        /* @var $model \common\models\TransportRequestsDialogs */
                        return Yii::$app->formatter->asDate($model->created_at, 'php:d.m.Y в H:i');
                    },
                    'options' => ['width' => '150'],
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                ],
                [
                    'attribute' => 'createdByName',
                    'format' => 'raw',
                    'value' => function($model, $key, $index, $column) use ($current_user_id) {
                        /* @var $model \common\models\TransportRequestsDialogs */
                        if ($model->created_by == $current_user_id)
                            return '<span class="text-muted">Вы</span>';
                        else
                            return $model->{$column->attribute};
                    },
                    'options' => ['width' => '200'],
                ],
                'message',
            ],
        ]); ?>

        <?php Pjax::end(); ?>

    </div>
</div>