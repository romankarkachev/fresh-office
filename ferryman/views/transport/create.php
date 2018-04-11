<?php

/* @var $this yii\web\View */
/* @var $model common\models\Transport */

$this->title = 'Новый автомобиль | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Транспорт', 'url' => ['/transport']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="transport-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
