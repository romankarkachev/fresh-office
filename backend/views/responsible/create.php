<?php

/* @var $this yii\web\View */
/* @var $model common\models\Responsible */

$this->title = 'Новое ответственное лицо | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Ответственные лица', 'url' => ['/responsible']];
$this->params['breadcrumbs'][] = 'Новое *';
?>
<div class="responsible-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
