<?php

/* @var $this yii\web\View */
/* @var $model common\models\pbxEmployees */

$this->title = 'Новый оператор Мини-АТС | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\PbxEmployeesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="pbx-employee-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
