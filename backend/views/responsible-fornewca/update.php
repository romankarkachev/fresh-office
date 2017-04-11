<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\ResponsibleFornewca */

$this->title = $model->responsible_name . HtmlPurifier::process(' &mdash; Ответственные лица (новые контрагенты) | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Ответственные лица (новые контрагенты)', 'url' => ['/responsible-fornewca']];
$this->params['breadcrumbs'][] = $model->responsible_name;
?>
<div class="responsible-fornewca-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
