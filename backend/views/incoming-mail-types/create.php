<?php

/* @var $this yii\web\View */
/* @var $model common\models\IncomingMailTypes */

$this->title = 'Новый вид входящей корреспонденции | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\IncomingMailTypesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="incoming-mail-types-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
