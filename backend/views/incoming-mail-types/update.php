<?php

use yii\helpers\HtmlPurifier;
use \backend\controllers\IncomingMailTypesController;

/* @var $this yii\web\View */
/* @var $model common\models\IncomingMailTypes */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . IncomingMailTypesController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = IncomingMailTypesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="incoming-mail-types-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
