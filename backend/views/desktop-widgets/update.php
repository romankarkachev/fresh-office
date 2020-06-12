<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\DesktopWidgetsController;

/* @var $this yii\web\View */
/* @var $model common\models\DesktopWidgets */
/* @var $newUsageModel common\models\TendersTp */
/* @var $dpUsage \yii\data\ActiveDataProvider of common\models\DesktopWidgets */

$this->title = HtmlPurifier::process('Виджет &laquo;' . $model->name . '&raquo; &mdash; ' . DesktopWidgetsController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = DesktopWidgetsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="desktop-widgets-update">
    <?= $this->render('_form', ['model' => $model]) ?>

    <?= $this->render('_usage_list', ['model' => $newUsageModel, 'dataProvider' => $dpUsage]); ?>

</div>
