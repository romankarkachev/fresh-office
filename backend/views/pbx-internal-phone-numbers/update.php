<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\PbxInternalPhoneNumbersController;

/* @var $this yii\web\View */
/* @var $model common\models\pbxInternalPhoneNumber */

$this->title = $model->phone_number . HtmlPurifier::process(' &mdash; ' . PbxInternalPhoneNumbersController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = PbxInternalPhoneNumbersController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->phone_number;
?>
<div class="pbx-internal-phone-number-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
