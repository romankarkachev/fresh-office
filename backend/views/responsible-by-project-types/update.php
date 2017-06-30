<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\ResponsibleByProjectTypes */

$this->title = $model->project_type_name . HtmlPurifier::process(' &mdash; Ответственные по типам проектов | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Ответственные по типам проектов', 'url' => ['/responsible-by-project-types']];
$this->params['breadcrumbs'][] = $model->project_type_name;
?>
<div class="responsible-by-project-types-update">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
