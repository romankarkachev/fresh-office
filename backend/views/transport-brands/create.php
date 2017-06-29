<?php

/* @var $this yii\web\View */
/* @var $model common\models\TransportBrands */

$this->title = 'Новая марка автомобиля | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Марки автомобилей', 'url' => ['/transport-brands']];
$this->params['breadcrumbs'][] = 'Новая *';
?>
<div class="transport-brands-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
