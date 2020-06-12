<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\TenderFormsController;

/* @var $this yii\web\View */
/* @var $model common\models\TenderFormsKinds */
/* @var $dpFields \yii\data\ActiveDataProvider of common\models\TenderFormsKindsFields[] */
/* @var $newFieldModel \common\models\TenderFormsKindsFields */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . TenderFormsController::LABEL_KINDS . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'] = TenderFormsController::BREADCRUMBS_KINDS;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="tenders-forms-kinds-update">
    <?= $this->render('_form', ['model' => $model]) ?>

    <?= $this->render('_fields_list', [
        'dataProvider' => $dpFields,
        'model' => $newFieldModel,
        'action' => TenderFormsController::URL_ADD_KIND_FIELD,
    ]); ?>

</div>
