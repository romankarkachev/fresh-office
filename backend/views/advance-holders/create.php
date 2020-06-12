<?php

/* @var $this yii\web\View */
/* @var $model common\models\FinanceAdvanceHolders */

$this->title = 'Выдача денежных средств подотчет | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\AdvanceHoldersController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Выдача подотчет *';
?>
<div class="finance-advance-holders-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
