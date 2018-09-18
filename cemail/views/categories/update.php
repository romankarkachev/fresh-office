<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\CEMailboxesCategories */

$this->title = $model->name . HtmlPurifier::process(' &mdash; Категории | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['/categories']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="cemailboxes-categories-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
