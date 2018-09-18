<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\PbxWebsitesController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\pbxWebsitesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = PbxWebsitesController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = PbxWebsitesController::ROOT_LABEL;
?>
<div class="pbx-websites-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

    </p>
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'columns' => [
            'name',
            [
                'attribute' => 'phonesCount',
                'options' => ['width' => '90'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
