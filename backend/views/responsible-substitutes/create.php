<?php

/* @var $this yii\web\View */
/* @var $model common\models\ResponsibleSubstitutes */

$this->title = 'Новое ответственное лицо | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Ответственные лица (подстановка)', 'url' => ['/responsible-substitutes']];
$this->params['breadcrumbs'][] = 'Новое *';
?>
<div class="responsible-substitutes-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
