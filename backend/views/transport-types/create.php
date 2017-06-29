<?php

/* @var $this yii\web\View */
/* @var $model common\models\TransportTypes */

$this->title = 'Новый тип техники | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Типы техники', 'url' => ['/transport-types']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="transport-types-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
