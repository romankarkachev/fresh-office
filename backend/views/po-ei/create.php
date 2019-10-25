<?php

/* @var $this yii\web\View */
/* @var $model common\models\PoEi */

$this->title = 'Новая статья расходов | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\PoEiController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="po-ei-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
