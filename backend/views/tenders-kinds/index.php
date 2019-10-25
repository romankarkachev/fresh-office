<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use \backend\controllers\TendersKindsController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TendersKindsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = TendersKindsController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = TendersKindsController::ROOT_LABEL;
?>
<div class="tenders-kinds-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'columns' => [
            'name',
            'keywords',
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
