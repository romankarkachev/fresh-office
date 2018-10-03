<?php

/* @var $this yii\web\View */
/* @var $model common\models\EcoTypes */

$this->title = 'Новый тип проекта | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\EcoTypesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="eco-types-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
