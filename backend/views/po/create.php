<?php

/* @var $this yii\web\View */
/* @var $model common\models\Po */
/* @var $dpProperties array */

$this->title = 'Новый платежный ордер по бюджету | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\PoController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="po-create">
    <?= $this->render('_form', ['model' => $model, 'dpProperties' => $dpProperties]) ?>

</div>
