<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\TransportByCitiesCost */

$this->title = 'Create Transport By Cities Cost';
$this->params['breadcrumbs'][] = ['label' => 'Transport By Cities Costs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transport-by-cities-cost-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
