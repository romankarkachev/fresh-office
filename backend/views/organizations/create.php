<?php

/* @var $this yii\web\View */
/* @var $model common\models\Organizations */

$this->title = 'Новая организация | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Организации', 'url' => ['/organizations']];
$this->params['breadcrumbs'][] = 'Новая *';
?>
<div class="organizations-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
