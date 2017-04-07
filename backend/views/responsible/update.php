<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\Responsible */

$this->title = $model->substitute_name . HtmlPurifier::process(' &mdash; Ответственные лица | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Ответственные лица', 'url' => ['/responsible']];
$this->params['breadcrumbs'][] = $model->substitute_name;
?>
<div class="responsible-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
