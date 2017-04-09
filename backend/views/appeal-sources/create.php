<?php

/* @var $this yii\web\View */
/* @var $model common\models\AppealSources */

$this->title = 'Новый источник обращения | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Источники обращения', 'url' => ['/appeal-sources']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="appeal-sources-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
