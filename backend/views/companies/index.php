<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\CompaniesController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\CompaniesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = CompaniesController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = CompaniesController::ROOT_LABEL;
?>
<div class="companies-list">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'name',
            'name_short',
            'inn',
            'kpp',
            'ogrn',
            //'address_j',
            //'address_f',
            //'dir_post',
            //'dir_name',
            //'dir_name_of',
            //'dir_name_short',
            //'dir_name_short_of',
            //'comment:ntext',
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
