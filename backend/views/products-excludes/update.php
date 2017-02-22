<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\ProductsExcludes */

$this->title = $model->name . HtmlPurifier::process(' &mdash; Исключения из номенклатуры | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Исключения из номенклатуры', 'url' => ['/products-excludes']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="products-excludes-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
