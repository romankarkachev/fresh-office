<?php

use backend\controllers\PoEiController;
use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\PoEi */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . PoEiController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = PoEiController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="po-ei-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
