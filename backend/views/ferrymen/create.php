<?php

/* @var $this yii\web\View */
/* @var $model common\models\Ferrymen */

$this->title = 'Новый перевозчик | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="ferrymen-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
