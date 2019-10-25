<?php

use yii\helpers\Html;
use common\models\StorageTtnRequired;
use backend\components\grid\GridView;
use backend\controllers\StorageTtnRequiredController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\StorageTtnRequiredSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = StorageTtnRequiredController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = StorageTtnRequiredController::ROOT_LABEL;
?>
<div class="storage-ttn-required-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'columns' => [
            [
                'attribute' => 'entity_id',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\StorageTtnRequired */
                    /* @var $column \yii\grid\DataColumn */

                    $type = '';

                    switch ($model->type) {
                        case StorageTtnRequired::TYPE_КОНТРАГЕНТ:
                            $type = 'Контрагент ';
                            break;
                        case StorageTtnRequired::TYPE_ОТВЕТСТВЕННЫЙ:
                            $type = 'Пользователь ';
                            break;
                        case StorageTtnRequired::TYPE_ПРОЕКТ:
                            $type = 'Проект ';
                            break;
                    }

                    return $type . $model->{$column->attribute};
                }
            ],
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>
</div>
