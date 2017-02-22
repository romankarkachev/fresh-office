<?php

/* @var $this yii\web\View */
/* @var $model common\models\ProductsExcludes */

$this->title = 'Новое исключение из номенклатуры | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Исключения из номенклатуры', 'url' => ['/products-excludes']];
$this->params['breadcrumbs'][] = 'Новое *';
?>
<div class="products-excludes-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
