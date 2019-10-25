<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\TendersPlatformsController;

/* @var $this yii\web\View */
/* @var $model common\models\TendersPlatforms */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . TendersPlatformsController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = TendersPlatformsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="tenders-platforms-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
