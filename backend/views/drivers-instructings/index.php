<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\DriversInstructingsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $driver common\models\Drivers */
/* @var $ferryman common\models\Ferrymen */

$this->title = 'Инструктажи водителя ' . $driver->name . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
$this->params['breadcrumbs'][] = ['label' => $ferryman->name, 'url' => ['/ferrymen/update', 'id' => $ferryman->id]];
$this->params['breadcrumbs'][] = ['label' => 'Водители', 'url' => ['/ferrymen-drivers', 'DriversSearch' => ['ferryman_id' => $ferryman->id]]];
$this->params['breadcrumbs'][] = ['label' => $driver->surname . ' ' . $driver->name, 'url' => ['/ferrymen-drivers/update', 'id' => $driver->id]];
$this->params['breadcrumbs'][] = 'Инструктажи водителя';
?>
<div class="drivers-instructings-list">
    <p>
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . $ferryman->name, ['/ferrymen/update', 'id' => $ferryman->id], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            'instructed_at:date',
            'place',
            'responsible',
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', ['/ferrymen/delete-instructing', 'id' => $model->id], ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                    }
                ],
                'visibleButtons' => [
                    'delete' => Yii::$app->user->can('root'),
                ],
                'options' => ['width' => '80'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

    <?= $this->render('_form', ['model' => new \common\models\DriversInstructings(['driver_id' => $driver->id])]); ?>

</div>
