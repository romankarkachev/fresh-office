<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\EcoContractsController;

/* @var $this yii\web\View */
/* @var $model common\models\EcoMc */
/* @var $newReportModel common\models\EcoMcTp */

$this->title = '№ ' . $model->id . HtmlPurifier::process(' &mdash; ' . EcoContractsController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = EcoContractsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = '№ ' . $model->id;
?>
<div class="eco-mc-update">
    <?= $this->render('_form', ['model' => $model, 'newReportModel' => $newReportModel]) ?>

</div>
