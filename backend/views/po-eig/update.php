<?php

use backend\controllers\PoEigController;
use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\PoEig */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . PoEigController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = PoEigController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="po-eig-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
