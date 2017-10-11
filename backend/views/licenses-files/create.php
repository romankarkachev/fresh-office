<?php

/* @var $this yii\web\View */
/* @var $model common\models\LicensesFiles */

$this->title = 'Новый скан | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Сканы лицензий организаций', 'url' => ['/licenses-files']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="licenses-files-create">
    <?= $this->render('_form', ['model' => $model]) ?>

</div>
