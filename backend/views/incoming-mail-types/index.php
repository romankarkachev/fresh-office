<?php

use yii\helpers\Html;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\IncomingMailTypesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Виды входящей корреспонденции | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Виды входящей корреспонденции';
?>
<div class="incoming-mail-types-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            'name',
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
