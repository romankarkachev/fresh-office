<?php

use yii\helpers\Html;
use yii\widgets\ListView;
use backend\controllers\AdvanceHoldersController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\FinanceAdvanceHoldersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = AdvanceHoldersController::ROOT_LABEL . ' | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = AdvanceHoldersController::ROOT_LABEL;

$iterator = -1;
?>

<div class="finance-advance-holders-list">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Новая операция', AdvanceHoldersController::URL_CREATE_AS_ARRAY, ['class' => 'btn btn-success']) ?>

    </p>
    <?php if ($dataProvider->totalCount > 0): ?>
    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '<div class="row">{items}</div><div class="clearfix"></div>{pager}',
        'itemOptions' => ['class' => 'col-xl-2 col-lg-2 col-md-3 col-xs-6'],
        'itemView' => function ($model, $key, $index, $widget) use ($iterator) {
            /* @var $model \common\models\FinanceAdvanceHolders */

            return $this->render('_item', ['item' => $model, 'index' => $index]);
        },
        'afterItem' => function ($model, $key, $index, $widget) {
            if (($index+1) % 6 == 0) {
                return '</div><div class="row">';
            }
            return null;
        },
    ]); ?>

    </div>
    <?php else: ?>
    <p>Подотчетничков нет.</p>
    <?php endif; ?>
</div>
