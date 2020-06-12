<?php

use common\models\ProductionSites;
use yii\helpers\Html;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ProductionSitesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = ProductionSites::LABEL_ROOT . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ProductionSites::LABEL_ROOT;
?>
<div class="production-sites-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'name',
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
