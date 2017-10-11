<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\LicensesFiles */

$this->title = $model->id . HtmlPurifier::process(' &mdash; Сканы лицензий организаций | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Сканы лицензий организаций', 'url' => ['/licenses-files']];
$this->params['breadcrumbs'][] = $model->id;
?>
<div class="licenses-files-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
