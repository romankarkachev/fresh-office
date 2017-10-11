<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\LicensesRequests */

$this->title = $model->id . HtmlPurifier::process(' &mdash; Запросы лицензий | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Запросы лицензий', 'url' => ['/licenses-requests']];
$this->params['breadcrumbs'][] = $model->id;
?>
<div class="licenses-requests-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
