<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\Documents */
/* @var $tprows common\models\DocumentsTp[] */
/* @var $hks common\models\DocumentsHk[] */

$this->title = '№ ' . $model->id . ' от ' . date('d.m.Y', strtotime($model->doc_date)) . HtmlPurifier::process(' &mdash; Документы | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Документы', 'url' => ['/documents']];
$this->params['breadcrumbs'][] = $model->id;
?>
<div class="documents-update">
    <?= $this->render('_form', [
        'model' => $model,
        'tprows' => $tprows,
        'hks' => $hks,
    ]) ?>

</div>
