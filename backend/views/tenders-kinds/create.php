<?php

/* @var $this yii\web\View */
/* @var $model common\models\TendersKinds */

$this->title = 'Новая разновидность конкурсов | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\TendersKindsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="tenders-kinds-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
