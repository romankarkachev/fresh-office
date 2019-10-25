<?php

/* @var $this yii\web\View */
/* @var $model common\models\TasksStates */

$this->title = 'Новый статус задач | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\TasksStatesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="tasks-states-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
