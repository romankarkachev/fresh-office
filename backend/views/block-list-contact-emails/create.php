<?php

/* @var $this yii\web\View */
/* @var $model common\models\CpBlContactEmails */

$this->title = 'Новый E-mail для исключения | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Исключенные из рассылки E-mail', 'url' => ['/block-list-contact-emails']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="cp-bl-contact-emails-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
