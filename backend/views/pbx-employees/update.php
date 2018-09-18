<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\PbxEmployeesController;

/* @var $this yii\web\View */
/* @var $model common\models\pbxEmployees */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . PbxEmployeesController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = PbxEmployeesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="pbx-employee-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
