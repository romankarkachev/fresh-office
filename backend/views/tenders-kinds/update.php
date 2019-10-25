<?php

use yii\helpers\HtmlPurifier;
use \backend\controllers\TendersKindsController;

/* @var $this yii\web\View */
/* @var $model common\models\TendersKinds */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . TendersKindsController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = TendersKindsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="tenders-kinds-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
