<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\TasksTypesController;

/* @var $this yii\web\View */
/* @var $model common\models\TasksTypes */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . TasksTypesController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = TasksTypesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="tasks-types-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
