<?php

/* @var $this yii\web\View */
/* @var $model common\models\Drivers */

$this->title = 'Новый водитель | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Водители', 'url' => ['/drivers']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="drivers-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
