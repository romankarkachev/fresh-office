<?php

/* @var $this yii\web\View */
/* @var $model common\models\TendersApplications */

$this->title = 'Новая форма подачи заявок на тендер | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\TendersApplicationsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="tenders-applications-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
