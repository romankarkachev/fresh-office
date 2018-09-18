<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\PbxInternalPhoneNumbersController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\pbxInternalPhoneNumberSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = PbxInternalPhoneNumbersController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = PbxInternalPhoneNumbersController::ROOT_LABEL;
?>
<div class="pbx-internal-phone-number-index">
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
            'phone_number',
            'departmentName',
            'employeeName',
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
