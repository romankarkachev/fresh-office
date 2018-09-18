<?php

/* @var $this yii\web\View */
/* @var $model common\models\pbxInternalPhoneNumber */

$this->title = 'Новый внутренний номер | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\PbxInternalPhoneNumbersController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="pbx-internal-phone-number-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
