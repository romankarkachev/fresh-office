<?php

/* @var $this yii\web\View */
/* @var $model common\models\PoAt */

$this->title = 'Новый шаблон автоплатежей | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\PoTemplatesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="po-at-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
