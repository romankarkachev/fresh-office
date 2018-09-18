<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\PbxEmployeesController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\pbxEmployeesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = PbxEmployeesController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = PbxEmployeesController::ROOT_LABEL;
?>
<div class="pbx-employees-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

    </p>
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            'name',
            'departmentName',
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
