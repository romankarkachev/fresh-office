<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\PbxWebsitesController;

/* @var $this yii\web\View */
/* @var $model common\models\pbxWebsites */
/* @var $dpPhones \yii\data\ActiveDataProvider */
/* @var $newPhoneModel \common\models\pbxExternalPhoneNumber*/

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . PbxWebsitesController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = PbxWebsitesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="pbx-websites-update">
    <?= $this->render('_form', ['model' => $model]) ?>

    <?= $this->render('external_phones_list', [
        'dataProvider' => $dpPhones,
        'model' => $newPhoneModel,
        'action' => PbxWebsitesController::URL_ADD_EXTERNAL_PHONE,
    ]); ?>

</div>
