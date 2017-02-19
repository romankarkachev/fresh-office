<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\HandlingKinds */

$this->title = 'Ошибка удаления вида обращения с отходами | '.Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Виды обращения', 'url' => ['/handling-kinds']];
?>
<div class="handling-kinds-cannot_delete">
    <div class="alert alert-danger">
        <h4><i class="fa fa-bolt"></i> Невозможно удалить запись &laquo<?= $model->name ?>&raquo;!</h4>
        <p>Элемент не может быть удален, поскольку используется в других объектах.</p>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Виды обращения', ['/handling-kinds'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список']) ?>

    </div>
</div>
