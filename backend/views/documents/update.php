<?php

use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model common\models\Documents */
/* @var $tprows common\models\DocumentsTp[] */
/* @var $hkrows common\models\DocumentsHk[] */
/* @var $hks common\models\HandlingKinds[] */

$this->title = '№ ' . $model->id . ' от ' . date('d.m.Y', strtotime($model->doc_date)) . HtmlPurifier::process(' &mdash; Документы | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Документы', 'url' => ['/documents']];
$this->params['breadcrumbs'][] = $model->id;
?>
<div class="documents-update">
    <?= $this->render('_form', [
        'model' => $model,
        'tprows' => $tprows,
        'hkrows' => $hkrows,
        'hks' => $hks,
    ]) ?>

</div>
