<?php

use backend\components\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model common\models\EdfDialogs */
/* @var $action string */

$current_user_id = Yii::$app->user->id;
?>

<div class="edf-dialogs">
    <div class="panel with-nav-tabs panel-success">
        <div class="panel-heading">Диалоги</div>
        <div class="panel-body">
            <div class="table-responsive">
                <?php Pjax::begin(['id' => 'pjax-dialogs' . $action, 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

                <?= $this->render('_dialog_form', [
                    'model' => $model,
                    'action' => $action,
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
                                /* @var $model \common\models\EdfDialogs */
                                return Yii::$app->formatter->asDate($model->created_at, 'php:d.m.Y в H:i');
                            },
                            'options' => ['width' => '150'],
                            'headerOptions' => ['class' => 'text-center'],
                            'contentOptions' => ['class' => 'text-center'],
                        ],
                        [
                            'attribute' => 'createdByProfileName',
                            'format' => 'raw',
                            'value' => function($model, $key, $index, $column) use ($current_user_id) {
                                /* @var $model \common\models\EdfDialogs */
                                if ($model->created_by == $current_user_id)
                                    return '<span class="text-muted">Вы</span>';
                                else
                                    return $model->{$column->attribute};
                            },
                            'options' => ['width' => '200'],
                        ],
                        [
                            'attribute' => 'roleName',
                            'options' => ['width' => '200'],
                        ],
                        'message',
                    ],
                ]); ?>

                <?php Pjax::end(); ?>

            </div>
        </div>
    </div>
</div>