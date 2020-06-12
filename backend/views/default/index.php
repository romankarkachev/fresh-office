<?php

/* @var $this yii\web\View */

$this->title = 'Добро пожаловать! | '.Yii::$app->name;
?>
<div class="jumbotron">
    <h1>Добро пожаловать!</h1>
    <p class="lead">Вас приветствует система управления документами компании.</p>
</div>

<?php if (!empty($widgets)): ?>
<div class="row">
    <?= $widgets; ?>

</div>
<?php endif; ?>
