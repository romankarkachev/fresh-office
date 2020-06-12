<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use backend\controllers\TenderFormsController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TenderFormsVarietiesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = TenderFormsController::LABEL_VARIETIES . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'] = TenderFormsController::BREADCRUMBS_VARIETIES_INDEX;
?>
<div class="tender-forms-varieties-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', Url::to(TenderFormsController::URL_CREATE_VARIETY_AS_ARRAY), ['class' => 'btn btn-success']) ?>

    </p>
    <?= \backend\components\grid\GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'name',
            [
                'class' => 'backend\components\grid\ActionColumn',
                'buttons' => [
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', Url::to(ArrayHelper::merge(TenderFormsController::URL_UPDATE_VARIETY_AS_ARRAY, ['id' => $model->id])), [
                            'title' => Yii::t('yii', 'Редактировать'),
                            'class' => 'btn btn-xs btn-default',
                        ]);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', Url::to(ArrayHelper::merge(TenderFormsController::URL_DELETE_VARIETY_AS_ARRAY, ['id' => $model->id])), [
                            'title' => Yii::t('yii', 'Удалить'),
                            'class' => 'btn btn-xs btn-danger',
                            'aria-label' => Yii::t('yii', 'Delete'),
                            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                            'data-method' => 'post',
                            'data-pjax' => '0',
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>

</div>
