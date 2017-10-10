<?php

/* @var $this yii\web\View */
/* @var $searchModel common\models\ProductionFeedbackFilesSearch */

$this->title = 'Обратная связь от производства | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Обратная связь от производства';
?>
<div class="production-feedback-files-nodata">
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => true]); ?>

    <p>Выполните настройку формы отбора.</p>
</div>
