<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\TasksPrioritiesController;

/* @var $this yii\web\View */
/* @var $model common\models\TasksPriorities */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . TasksPrioritiesController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = TasksPrioritiesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="tasks-priorities-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
