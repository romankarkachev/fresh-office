<?php

/* @var $this yii\web\View */
/* @var $model common\models\EcoMilestones */

$this->title = 'Новый этап проекта | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\EcoMilestonesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="eco-milestones-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
