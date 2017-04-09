<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\AppealSources */

$this->title = $model->name . HtmlPurifier::process(' &mdash; Источники обращения | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Источники обращения', 'url' => ['/appeal-sources']];
$this->params['breadcrumbs'][] = $model->name;
?>
<div class="appeal-sources-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
