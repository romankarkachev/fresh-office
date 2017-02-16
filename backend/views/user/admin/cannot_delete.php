<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Users */

$this->title = 'Ошибка удаления пользователя | '.Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['/users']];

$this->params['content-block'] = 'Удаление пользователей';
$this->params['content-additional'] = 'Невозможно удалить пользователя.';
?>
<div class="users-cannot_delete">
    <div class="alert alert-danger">
        <h4><i class="fa fa-bolt"></i> Невозможно удалить запись &laquo<?= $model->profile->surname . ' ' . $model->profile->name ?>&raquo;!</h4>

        <p>Элемент не может быть удален, поскольку используется в других объектах.</p>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Пользователи', ['/users'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список']) ?>

    </div>
</div>
