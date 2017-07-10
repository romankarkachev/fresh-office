<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\TransportByCitiesCost */

$this->title = 'Update Transport By Cities Cost: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Transport By Cities Costs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="transport-by-cities-cost-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
