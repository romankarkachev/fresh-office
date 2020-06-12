<?php

/* @var $this yii\web\View */
/* @var $model common\models\TenderFormsKinds */

$this->title = 'Новая форма | ' . Yii::$app->name;
$this->params['breadcrumbs'] = \backend\controllers\TenderFormsController::BREADCRUMBS_KINDS;
$this->params['breadcrumbs'][] = 'Новая *';
?>
<div class="tenders-forms-kinds-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
