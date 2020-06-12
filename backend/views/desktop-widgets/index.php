<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\DesktopWidgetsController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\DesktopWidgetsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = DesktopWidgetsController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = DesktopWidgetsController::ROOT_LABEL;
?>
<div class="desktop-widgets-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'columns' => [
            'name',
            'description:ntext',
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
