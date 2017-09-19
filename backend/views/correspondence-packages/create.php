<?php

/* @var $this yii\web\View */
/* @var $model common\models\CorrespondencePackages */

$this->title = 'Новый пакет корреспонденции | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Пакеты корреспонденции', 'url' => ['/correspondence-packages']];
$this->params['breadcrumbs'][] = 'Новый *';
?>
<div class="correspondence-packages-create">
    <?= $this->render('_form_new', ['model' => $model]) ?>

</div>
