<?php

/* @var $this yii\web\View */
/* @var $model common\models\CEMailboxesCategories */

$this->title = 'Новая категория | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['/categories']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="cemailboxes-categories-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
