<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\CEMailboxes */

$this->title = $model->name . HtmlPurifier::process(' &mdash; Почтовые ящики | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Почтовые ящики', 'url' => ['/mailboxes']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="cemailboxes-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
