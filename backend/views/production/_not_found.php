<?php

/* @var $this yii\web\View */
/* @var $model common\models\PackingTypes */

$this->title = 'Новый вид упаковки | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Виды упаковки', 'url' => ['/packing-types']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="packing-types-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
