<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\EcoTypesController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\EcoTypesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = EcoTypesController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = EcoTypesController::ROOT_LABEL;
?>
<div class="eco-types-list">
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
