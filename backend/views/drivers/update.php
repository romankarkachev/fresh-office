<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\Drivers */

$modelRepresentation = $model->surname . ' ' . $model->name . ' ' . $model->patronymic;

$this->title = $modelRepresentation . HtmlPurifier::process(' &mdash; Водители | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Перевозчики', 'url' => ['/ferrymen']];
$this->params['breadcrumbs'][] = ['label' => $model->ferryman->name, 'url' => ['/ferrymen/update', 'id' => $model->ferryman->id]];
$this->params['breadcrumbs'][] = ['label' => 'Водители', 'url' => ['/ferrymen-drivers', 'DriversSearch' => ['ferryman_id' => $model->ferryman->id]]];
$this->params['breadcrumbs'][] = $modelRepresentation;
?>
<div class="drivers-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
