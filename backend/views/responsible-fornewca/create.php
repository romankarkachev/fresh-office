<?php

/* @var $this yii\web\View */
/* @var $model common\models\ResponsibleFornewca */

$this->title = 'Новое ответственное лицо | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Ответственные лица (новые контрагенты)', 'url' => ['/responsible-fornewca']];
$this->params['breadcrumbs'][] = 'Новое *';
?>
<div class="responsible-fornewca-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
