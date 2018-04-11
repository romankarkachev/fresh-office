<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $details array */

$this->title = 'Ошибка доступа | '.Yii::$app->name;
$this->params['breadcrumbs'][] = $details['breadcrumbs'];

if (isset($details['modelRep'])) $modelRep = ' &laquo' . $details['modelRep'] . '&raquo;';
?>
<div class="record-forbidden_foreign">
    <div class="alert alert-danger" role="alert">
        <h4><i class="fa fa-bolt"></i> Невозможно открыть запись<?= $modelRep ?>!</h4>
        <p>Элемент не может быть отображен, поскольку не принадлежит пользователю, под которым Вы авторизованы.</p>
        <hr>
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> '. $details['buttonCaption'], $details['buttonUrl'], ['class' => 'btn btn-outline-primary btn-lg', 'title' => 'Вернуться в список']) ?>

    </div>
</div>
