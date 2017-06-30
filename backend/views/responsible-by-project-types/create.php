<?php

/* @var $this yii\web\View */
/* @var $model common\models\ResponsibleByProjectTypes */

$this->title = 'Новое ответственное лицо по типам проектов | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Ответственные лица по типам проектов', 'url' => ['/responsible-by-project-types']];
$this->params['breadcrumbs'][] = 'Новое *';
?>
<div class="responsible-by-project-types-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
