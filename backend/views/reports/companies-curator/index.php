<?php

/* @var $this yii\web\View */
/* @var $searchModel common\models\ReportPoAnalytics */
/* @var $dataProvider yii\data\ArrayDataProvider */

$this->title = 'Выборка контрагентов, для которых куратором назначен указанный в отборе менеджер | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Контрагенты куратора';
?>
<div class="reports-companies-curator">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <?= \backend\components\grid\GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'ID_COMPANY',
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '60'],
            ],
            'companyName',
        ],
    ]); ?>

</div>
