<?php

/* @var $this yii\web\View */
/* @var $model common\models\Appeals */

$this->title = 'Новое обращение | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Обращения', 'url' => ['/appeals']];
$this->params['breadcrumbs'][] = 'Новое *';
?>
<div class="appeals-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
