<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\PoTemplatesController;

/* @var $this yii\web\View */
/* @var $model common\models\PoAt */

$this->title = $model->comment . HtmlPurifier::process(' &mdash; ' . PoTemplatesController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = PoTemplatesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->comment;
?>
<div class="po-at-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
