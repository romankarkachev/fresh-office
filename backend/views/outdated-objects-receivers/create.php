<?php

/* @var $this yii\web\View */
/* @var $model common\models\OutdatedObjectsReceivers */

$this->title = 'Новый получатель | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\OutdatedObjectsReceiversController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="outdated-objects-receivers-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
