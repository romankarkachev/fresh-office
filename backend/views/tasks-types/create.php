<?php

/* @var $this yii\web\View */
/* @var $model common\models\TasksTypes */

$this->title = 'Новый тип задач | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\TasksTypesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="tasks-types-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
