<?php

/* @var $this yii\web\View */
/* @var $model common\models\Companies */

$this->title = 'Новый контрагент | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\CompaniesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="companies-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
