<?php

/* @var $this yii\web\View */
/* @var $model common\models\Appeals */

$this->title = 'Мастер обработки обращений | ' . Yii::$app->name;
if (Yii::$app->user->can('root') || Yii::$app->user->can('role_report1'))
    $this->params['breadcrumbs'][] = ['label' => 'Обращения', 'url' => ['/appeals']];
$this->params['breadcrumbs'][] = 'Мастер обработки обращений';
?>
<div class="appeals-wizard-empty-dataset">
    <div class="alert alert-success" role="alert">
        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
        <strong>Нет обращений.</strong><br />
        Обновите страницу, чтобы увидеть вновь появившиеся обращения.
    </div>
</div>
