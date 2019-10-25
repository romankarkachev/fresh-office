<?php

/* @var $this yii\web\View */
/* @var $model common\models\TendersPlatforms */

$this->title = 'Новая тендерная площадка | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\TendersPlatformsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="tenders-platforms-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
