<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\StorageTtnRequiredController;

/* @var $this yii\web\View */
/* @var $model common\models\StorageTtnRequired */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . StorageTtnRequiredController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = StorageTtnRequiredController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="storage-ttn-required-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
