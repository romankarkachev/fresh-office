<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\grid\GridView;
use backend\controllers\PoEiController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PoEiSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = PoEiController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = PoEiController::ROOT_LABEL;
?>
<div class="po-ei-list">
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>
    <div class="row form-group">
        <div class="col-md-6">
            <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

            <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

        </div>
        <div class="col-md-6 text-right">
            <?= Html::a('<i class="fa fa-cogs"></i> Массовая замена', Url::to([PoEiController::URL_MASS_REPLACE]), ['class' => 'btn btn-default', 'title' => 'Массовая замена статей расходов в платежных ордерах']) ?>

        </div>
    </div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'groupName',
            'name',
            ['class' => 'backend\components\grid\ActionColumn'],
        ],
    ]); ?>

</div>
