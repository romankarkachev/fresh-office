<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\CpBlContactEmails */

$this->title = $model->email . HtmlPurifier::process(' &mdash; Исключенные из рассылки E-mail | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Исключенные из рассылки E-mail', 'url' => ['/block-list-contact-emails']];
$this->params['breadcrumbs'][] = $model->email;
?>
<div class="cp-bl-contact-emails-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
