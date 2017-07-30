<?php

/* @var $this yii\web\View */
/* @var $model common\models\PadKinds */

$this->title = 'Новый вид документов | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Виды документов', 'url' => ['/pad-kinds']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="pad-kinds-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
