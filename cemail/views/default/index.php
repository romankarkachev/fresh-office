<?php

/* @var $this yii\web\View */
/* @var $totalLettersCount integer общее количество писем в системе по всем ящикам */

$this->title = 'Добро пожаловать! | '.Yii::$app->name;
?>
<div class="welcome-dashboard" style="padding-top: 1.5rem;">
    <div class="jumbotron">
        <h1>Добро пожаловать!</h1>
        <p class="lead">
            Вы находитесь в разделе управления корпоративной почтой. На данный момент в системе
            <?= \common\models\foProjects::declension($totalLettersCount, ['письмо', 'письма', 'писем']) ?>.
        </p>
    </div>
</div>
<?php
$this->registerJs(<<<JS

JS
, \yii\web\View::POS_READY);
?>
