<?php

/* @var $this yii\web\View */
/* @var $model common\models\EcoMc */

$this->title = 'Новый договор сопровождения по экологии | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\EcoContractsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="eco-mc-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
