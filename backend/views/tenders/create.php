<?php

/* @var $this yii\web\View */
/* @var $model common\models\Tenders */

$this->title = 'Новый тендер | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = \backend\controllers\TendersController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="tenders-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
<?php
$this->registerJs(<<<JS
initializeCheckboxes();
JS
, yii\web\View::POS_READY);
?>
