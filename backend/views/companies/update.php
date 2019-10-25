<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\CompaniesController;

/* @var $this yii\web\View */
/* @var $model common\models\Companies */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . CompaniesController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = CompaniesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="companies-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
