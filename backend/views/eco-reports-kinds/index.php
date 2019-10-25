<?php

use yii\helpers\Html;
use backend\components\grid\GridView;
use backend\controllers\EcoReportsKindsController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\EcoReportsKindsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Разновидности отчетов для контролирующих органов | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = EcoReportsKindsController::MAIN_MENU_LABEL;
?>
<div class="eco-reports-kinds-list">
    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'columns' => [
            'name',
            'gov_agency',
            'periodicityName',
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
