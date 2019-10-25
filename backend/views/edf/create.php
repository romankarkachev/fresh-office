<?php

/* @var $this yii\web\View */
/* @var $model common\models\Edf */
/* @var $tp common\models\EdfTp[] */

$this->title = 'Новый электронный документ | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\EdfController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="edf-create">
    <?= $this->render('_form', ['model' => $model, 'tp' => $tp, 'hasAccess' => $hasAccess]) ?>

</div>
