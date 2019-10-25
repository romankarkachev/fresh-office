<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\TasksStatesController;

/* @var $this yii\web\View */
/* @var $model common\models\TasksStates */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . TasksStatesController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = TasksStatesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="tasks-states-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
