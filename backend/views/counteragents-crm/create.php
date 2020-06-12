<?php

/* @var $this yii\web\View */
/* @var $model common\models\foCompany */

$this->title = 'Новый контрагент CRM | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\CounteragentsCrmController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="fo-company-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
