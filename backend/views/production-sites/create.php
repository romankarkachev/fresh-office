<?php

use common\models\ProductionSites;

/* @var $this yii\web\View */
/* @var $model common\models\ProductionSites */

$this->title = 'Новая производственная площадка | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => ProductionSites::LABEL_ROOT, 'url' => ProductionSites::URL_ROOT_ROUTE_AS_ARRAY];
$this->params['breadcrumbs'][] = 'Новая *';
?>
<div class="production-sites-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
