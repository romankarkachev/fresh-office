<?php

/* @var $this yii\web\View */
/* @var $model common\models\Units */

$this->title = 'Новая единица измерения | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Единицы измерения', 'url' => ['/units']];
$this->params['breadcrumbs'][] = 'Новая *';
?>
<div class="units-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
