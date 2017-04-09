<?php

/* @var $this yii\web\View */
/* @var $model common\models\ResponsibleRefusal */

$this->title = 'Новое ответственное лицо | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Ответственные лица (отказ)', 'url' => ['/responsible-refusal']];
$this->params['breadcrumbs'][] = 'Новое *';
?>
<div class="responsible-refusal-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
