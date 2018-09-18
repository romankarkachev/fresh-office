<?php

use yii\helpers\Html;
use yii\helpers\Url;
use ferryman\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\CEAttachedFilesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = 'Вложения к письмам | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Вложения к письмам';

//$this->params['breadcrumbsRight'][] = ['label' => 'Фильтр', 'icon' => 'fa fa-filter', 'url' => '#frmSearch', 'data-target' => '#frmSearch', 'data-toggle' => 'collapse', 'aria-expanded' => $searchApplied === true ? 'true' : 'false', 'aria-controls' => 'frmSearch'];
$this->params['breadcrumbsRight'][] = ['icon' => 'fa fa-sort-amount-asc', 'url' => ['/attached-files'], 'title' => 'Сбросить отбор и применить сортировку по-умолчанию'];
?>
<div class="attached-files-list">
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <div class="card">
        <div class="card-block">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'message_id',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $column) use ($urlToggleActivity) {
                            /** @var \common\models\CEAttachedFiles $model */
                            /** @var \yii\grid\DataColumn $column */

                            return Html::a($model->{$column->attribute} . ' <i class="fa fa-share-square-o"></i>', Url::to(['/mail/view/' . $model->{$column->attribute}]), ['target' => '_blank', 'data-pjax' => '0']);
                        },
                        'visible' => false,
                    ],
                    [
                        'attribute' => 'ofn',
                        'format' => 'raw',
                        'value' => function ($model, $key, $index, $column) use ($urlToggleActivity) {
                            /** @var \common\models\CEAttachedFiles $model */
                            /** @var \yii\grid\DataColumn $column */

                            $result = '';
                            $ids = explode(',', $model->lettersIds);
                            if (count($ids) >= 1) {
                                foreach ($ids as $attach) {
                                    $result .= Html::a($attach, Url::to(['/mail/view/' . trim($attach)]), ['target' => '_blank', 'data-pjax' => '0']) . ', ';
                                }
                                $result = '<br /><small>' . trim($result, ', ') . '</small>';
                            }

                            return $model->ofn . $result;
                        }
                    ],
                    'size:shortSize',
                ],
            ]); ?>

        </div>
    </div>
</div>
