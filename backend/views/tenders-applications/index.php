<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\TendersApplicationsController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TendersApplicationsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = TendersApplicationsController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = TendersApplicationsController::ROOT_LABEL;
?>
<div class="tenders-applications-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'columns' => [
            'name',
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
