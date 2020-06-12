<?php

/* @var $this yii\web\View */
/* @var $model common\models\DesktopWidgets */

$this->title = 'Новый виджет для Рабочего стола | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\DesktopWidgetsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="desktop-widgets-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
