<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\TendersApplicationsController;

/* @var $this yii\web\View */
/* @var $model common\models\TendersApplications */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . TendersApplicationsController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = TendersApplicationsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="tenders-applications-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
