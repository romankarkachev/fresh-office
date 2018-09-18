<?php

/* @var $this yii\web\View */
/* @var $model common\models\pbxWebsites */

$this->title = 'Новый сайт | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\PbxWebsitesController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="pbx-websites-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
