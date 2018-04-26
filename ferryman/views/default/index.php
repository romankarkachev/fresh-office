<?php

/* @var $this yii\web\View */

$this->title = 'Добро пожаловать! | '.Yii::$app->name;
?>
<div class="welcome-dashboard">
    <p>&nbsp;</p>
    <div class="jumbotron">
        <h1>Добро пожаловать!</h1>

        <p class="lead">Вы находитесь в личном кабинете перевозичка.</p>

        <p><?= \yii\helpers\Html::a('Мои рейсы', ['/freights'], ['class' => 'btn btn-lg btn-success']) ?></p>
    </div>
</div>
