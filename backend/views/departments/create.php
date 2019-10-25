<?php

/* @var $this yii\web\View */
/* @var $model common\models\Departments */

$this->title = 'Новый контрагент | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\DepartmentsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="departments-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
