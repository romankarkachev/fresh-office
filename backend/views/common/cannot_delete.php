<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $details array */

$this->title = 'Ошибка удаления записи | '.Yii::$app->name;
$this->params['breadcrumbs'][] = $details['breadcrumbs'];
?>
<div class="counteragents-cannot_delete">
    <div class="alert alert-danger" role="alert">
        <h4><i class="fa fa-bolt"></i> Невозможно удалить запись &laquo<?= $details['modelRep'] ?>&raquo;!</h4>
        <p>Элемент не может быть удален, поскольку используется в других объектах.</p>
        <hr>
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> '. $details['buttonCaption'], $details['buttonUrl'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список']) ?>

    </div>
</div>
