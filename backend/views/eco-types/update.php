<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\EcoTypesController;

/* @var $this yii\web\View */
/* @var $model common\models\EcoTypes */
/* @var $dpMilestones \yii\data\ActiveDataProvider */
/* @var $newMilestoneModel \common\models\EcoTypesMilestones */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . EcoTypesController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = EcoTypesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="eco-types-update">
    <?= $this->render('_form', ['model' => $model]) ?>

    <?= $this->render('milestones_list', [
        'dataProvider' => $dpMilestones,
        'model' => $newMilestoneModel,
        'action' => EcoTypesController::URL_ADD_MILESTONE,
    ]); ?>

</div>
