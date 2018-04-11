<?php

/* @var $this yii\web\View */
/* @var $model common\models\FerrymenBankDetails */

$this->title = 'Новый банковский счет | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Банковские счета', 'url' => ['/bank-accounts']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="bank-accounts-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
