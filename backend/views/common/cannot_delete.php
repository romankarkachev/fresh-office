<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $details array */

$this->title = 'Ошибка удаления записи | '.Yii::$app->name;
$this->params['breadcrumbs'][] = $details['breadcrumbs'];

$modelRep = '';
if (!empty($details['modelRep'])) $modelRep = ' &laquo' . $details['modelRep'] .'&raquo;';
?>
<div class="counteragents-cannot_delete">
    <div class="alert alert-danger" role="alert">
        <h4><i class="fa fa-bolt"></i> Невозможно <?= $details['action1'] ?> запись<?= $modelRep ?>!</h4>
        <p>Элемент не может быть удален, поскольку используется в других объектах.</p>
        <hr>
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> '. $details['buttonCaption'], $details['buttonUrl'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список']) ?>

    </div>
</div>
