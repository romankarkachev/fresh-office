<?php

/* @var $this yii\web\View */
/* @var $model common\models\TasksPriorities */

$this->title = 'Новый приоритет задач | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\TasksPrioritiesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="tasks-priorities-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
