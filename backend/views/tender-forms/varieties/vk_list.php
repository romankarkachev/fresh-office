<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use common\models\TenderFormsVarietiesKinds;
use backend\controllers\TenderFormsController;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $action string */

$templateBaseDir = TenderFormsVarietiesKinds::getUploadsFilepath();
?>
<div class="varieties-kinds-list">
    <?php Pjax::begin(['id' => 'pjax-vk' . $action, 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

    <?= $this->render('_vk_form', [
        'dataProvider' => $dataProvider,
        'model' => $model,
        'action' => $action,
    ]); ?>

    <?= \backend\components\grid\GridView::widget([
        'id' => 'gwKinds',
        'dataProvider' => $dataProvider,
        'layout' => '{items}',
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '35'],
            ],
            'kindName',
            [
                'label' => 'Путь',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use ($templateBaseDir) {
                    /* @var $model \common\models\TenderFormsVarietiesKinds */
                    /* @var $column \yii\grid\DataColumn */

                    return $templateBaseDir . '/' . $model->variety_id . '/' . $model->kind_id . '.docx ' .
                        Html::a(
                            'Обновить шаблон',
                            Url::to(ArrayHelper::merge(TenderFormsController::URL_UPDATE_VK_TEMPLATE_AS_ARRAY, ['id' => $model->id])),
                            ['data-pjax' => '0']
                        );
                },
            ],
            [
                'label' => 'Наличие',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) use ($templateBaseDir) {
                    /* @var $model \common\models\TenderFormsVarietiesKinds */
                    /* @var $column \yii\grid\DataColumn */

                    if (file_exists($templateBaseDir . '/' . $model->variety_id . '/' . $model->kind_id . '.docx')) {
                        return '<i class="fa fa-check-circle-o text-success"></i>';
                    }
                    else {
                        return '-';
                    }
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '30'],
            ],
            [
                'class' => 'backend\components\grid\ActionColumn',
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', [TenderFormsController::URL_DELETE_VARIETY_KIND, 'id' => $model->id], ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => true]);
                    }
                ],
                'options' => ['width' => '20'],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
