<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\DepartmentsController;

/* @var $this yii\web\View */
/* @var $model common\models\Departments */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . DepartmentsController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = DepartmentsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="departments-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
