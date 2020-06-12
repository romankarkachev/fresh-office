<?php

use yii\helpers\Url;
use ferryman\components\grid\GridView;
use common\components\grid\TotalsColumn;

/* @var $this yii\web\View */
/* @var $searchModel common\models\foProjectsSearch */
/* @var $dataProvider yii\data\ArrayDataProvider */
/* @var $queryString string */

$this->title = 'Рейсы | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Рейсы';

$this->params['breadcrumbsRight'][] = ['label' => 'Экспорт в Excel', 'icon' => 'fa fa-file-excel-o fa-lg', 'url' => Url::to(['/freights']) . '?export=true' . $queryString, 'class' => 'btn'];
?>
<div class="freights-list">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <div class="card">
        <div class="card-block">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'showFooter' => true,
                'columns' => [
                    [
                        'attribute' => 'id',
                        'label' => '№ проекта',
                        'options' => ['width' => '120'],
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'vivozdate',
                        'format' => 'date',
                        'options' => ['width' => '120'],
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'footer' => '<strong>Итого:</strong>',
                        'footerOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'class' => TotalsColumn::className(),
                        'attribute' => 'cost',
                        'label' => 'Стоимость',
                        'format' => ['decimal', 'decimals' => 2],
                        'options' => ['width' => '120'],
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-right'],
                        'footerOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'attribute' => 'oplata',
                        'format' => 'date',
                        'options' => ['width' => '120'],
                        'headerOptions' => ['class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'ttn',
                        'options' => ['width' => '60'],
                    ],
                    'adres',
                ],
            ]); ?>

        </div>
    </div>
</div>
