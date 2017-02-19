<?php

/* @var $this yii\web\View */
/* @var $model common\models\Documents */

$this->title = 'Новый документ | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Документы', 'url' => ['/documents']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="documents-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
