<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\EcoMilestonesController;

/* @var $this yii\web\View */
/* @var $model common\models\EcoMilestones */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . EcoMilestonesController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = EcoMilestonesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="eco-milestones-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
