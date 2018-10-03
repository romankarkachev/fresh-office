<?php

/* @var $this yii\web\View */
/* @var $model common\models\EcoProjects */

$this->title = 'Новый проект по экологии | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\EcoProjectsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="eco-projects-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
