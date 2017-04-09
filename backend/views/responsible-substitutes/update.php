<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\ResponsibleSubstitutes */

$this->title = $model->substitute_name . HtmlPurifier::process(' &mdash; Ответственные лица (замена) | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Ответственные лица (замена)', 'url' => ['/responsible-substitutes']];
$this->params['breadcrumbs'][] = $model->substitute_name;
?>
<div class="responsible-substitutes-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
