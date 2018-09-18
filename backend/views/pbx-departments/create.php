<?php

/* @var $this yii\web\View */
/* @var $model common\models\pbxDepartments */

$this->title = 'Новый отдел компании в Мини-АТС | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\PbxDepartmentsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="pbx-department-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
