<?php

/* @var $this yii\web\View */
/* @var $model common\models\StorageTtnRequired */

$this->title = 'Новый контрагент или проект | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\StorageTtnRequiredController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="storage-ttn-required-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
