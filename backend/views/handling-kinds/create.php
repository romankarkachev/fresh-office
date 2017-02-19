<?php

/* @var $this yii\web\View */
/* @var $model common\models\HandlingKinds */

$this->title = 'Новый вид обращения с отходами | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Виды обращения', 'url' => ['/handling-kinds']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="handling-kinds-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
