<?php

use yii\helpers\HtmlPurifier;
use backend\controllers\TenderFormsController;

/* @var $this yii\web\View */
/* @var $model common\models\TenderFormsVarieties */
/* @var $dpKinds \yii\data\ActiveDataProvider */
/* @var $newVkModel \common\models\TenderFormsVarietiesKinds */

$this->title = $model->name . HtmlPurifier::process(' &mdash; ' . TenderFormsController::LABEL_VARIETIES . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'] = TenderFormsController::BREADCRUMBS_VARIETIES;
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="tender-forms-varieties-update">
    <?= $this->render('_form', ['model' => $model]) ?>

    <?= $this->render('vk_list', [
        'dataProvider' => $dpKinds,
        'model' => $newVkModel,
        'action' => TenderFormsController::URL_ADD_VARIETY_KIND,
    ]); ?>

</div>
