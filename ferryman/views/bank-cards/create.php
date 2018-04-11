<?php

/* @var $this yii\web\View */
/* @var $model common\models\FerrymenBankCards */

$this->title = 'Новая банковская карта | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Банковские карты', 'url' => ['/bank-cards']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="bank-cards-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
