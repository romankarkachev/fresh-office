<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\Appeals */

$this->title = 'Обращение № ' . $model->id . ' (' . $model->form_username . ') ' . HtmlPurifier::process('&mdash; Обращения | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Обращения', 'url' => ['/appeals']];
$this->params['breadcrumbs'][] = '№ ' . $model->id;
?>
<div class="appeals-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
