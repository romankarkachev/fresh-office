<?php

/* @var $this yii\web\View */
/* @var $model common\models\Tasks */

$this->title = 'Новая задача | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\TasksController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="tasks-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
