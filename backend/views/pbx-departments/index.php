<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\PbxDepartmentsController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\pbxDepartmentsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = PbxDepartmentsController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = PbxDepartmentsController::ROOT_LABEL;
?>
<div class="pbx-department-list">
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
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
