<?php

/* @var $this yii\web\View */
/* @var $model common\models\EcoReportsKinds */

$this->title = 'Новый регламентированный отчет | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\EcoReportsKindsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="eco-reports-kinds-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
