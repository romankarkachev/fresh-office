<?php

/* @var $this yii\web\View */
/* @var $model common\models\ResponsibleForProduction */

$this->title = 'Новый получатель корреспонденции от производства | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Получатели корреспонденции от производства', 'url' => ['/responsible-for-production']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="responsible-for-production-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
