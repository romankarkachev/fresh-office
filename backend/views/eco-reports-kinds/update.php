<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\EcoReportsKindsController;

/* @var $this yii\web\View */
/* @var $model common\models\EcoReportsKinds */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . EcoReportsKindsController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = EcoReportsKindsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="eco-reports-kinds-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
