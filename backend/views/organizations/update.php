<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\OrganizationsController;

/* @var $this yii\web\View */
/* @var $model common\models\Organizations */
/* @var $dpBankAccounts \yii\data\ActiveDataProvider */
/* @var $newBankAccountModel \common\models\OrganizationsBas */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . OrganizationsController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = OrganizationsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="organizations-update">
    <?= $this->render('_form', ['model' => $model]) ?>

    <?= $this->render('ba_list', [
        'dataProvider' => $dpBankAccounts,
        'model' => $newBankAccountModel,
        'action' => OrganizationsController::URL_ADD_BANK_ACCOUNT,
    ]); ?>

</div>
