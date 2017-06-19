<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\ProjectsFO */

$this->title = $model->id . HtmlPurifier::process(' &mdash; Проекты | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Проекты', 'url' => ['/projects']];
$this->params['breadcrumbs'][] = $model->id;
?>
<div class="projects-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
