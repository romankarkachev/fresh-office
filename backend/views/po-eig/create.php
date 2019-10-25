<?php

/* @var $this yii\web\View */
/* @var $model common\models\PoEig */

$this->title = 'Новая группа статей расходов | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\PoEigController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="po-eig-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
