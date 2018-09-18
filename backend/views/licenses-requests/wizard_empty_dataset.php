<?php

/* @var $this yii\web\View */
/* @var $model common\models\LicensesRequests */

$this->title = 'Мастер обработки запросов лицензий | ' . Yii::$app->name;
if (Yii::$app->user->can('root') || Yii::$app->user->can('sales_department_head'))
    $this->params['breadcrumbs'][] = ['label' => 'Запросы лицензий', 'url' => ['/licenses-requests']];
$this->params['breadcrumbs'][] = 'Мастер обработки запросов лицензий';
?>
<div class="licenses-requests-wizard-empty-dataset">
    <div class="alert alert-success" role="alert">
        <i class="fa fa-check-circle-o" aria-hidden="true"></i>
        <strong>Нет запросов лицензий.</strong><br />
    </div>
    <p>Обновите страницу, чтобы увидеть вновь появившиеся запросы лицензий.</p>
</div>
