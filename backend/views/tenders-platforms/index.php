<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\TendersPlatformsController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TendersPlatformsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = TendersPlatformsController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = TendersPlatformsController::ROOT_LABEL;
?>
<div class="tenders-platforms-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'columns' => [
            'name',
            'href',
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
