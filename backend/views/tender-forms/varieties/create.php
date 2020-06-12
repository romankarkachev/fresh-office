<?php

/* @var $this yii\web\View */
/* @var $model common\models\TenderFormsVarieties */

$this->title = 'Новая разновидность набора форм | ' . Yii::$app->name;
$this->params['breadcrumbs'] = \backend\controllers\TenderFormsController::BREADCRUMBS_VARIETIES;
$this->params['breadcrumbs'][] = 'Новая *';
?>
<div class="tender-forms-varieties-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
