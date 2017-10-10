<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\ResponsibleForProduction */

$this->title = $model->receiver . HtmlPurifier::process(' &mdash; Получатели корреспонденции от производства | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Получатели корреспонденции от производства', 'url' => ['/responsible-for-production']];
$this->params['breadcrumbs'][] = $model->receiver;
?>
<div class="responsible-for-production-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
