<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\PbxDepartmentsController;

/* @var $this yii\web\View */
/* @var $model common\models\pbxDepartments */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . PbxDepartmentsController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = PbxDepartmentsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="pbx-department-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
