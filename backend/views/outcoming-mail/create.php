<?php

/* @var $this yii\web\View */
/* @var $model common\models\IncomingMail */

$this->title = 'Новый элемент исходящей корреспонденции | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\OutcomingMailController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="incoming-mail-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
