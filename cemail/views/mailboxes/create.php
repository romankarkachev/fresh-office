<?php

/* @var $this yii\web\View */
/* @var $model common\models\CEMailboxes */

$this->title = 'Новый почтовый ящик | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Почтовые ящики', 'url' => ['/mailboxes']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="cemailboxes-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
