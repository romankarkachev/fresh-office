<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="default-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)) ?>
    </div>

    <p>
        При выполнении Вашего запроса произошла ошибка, текст которой предоставлен выше.
    </p>
    <p>
        Если Вы считаете, что запрос выполнен верно, обратитесь к администрации сайта. Можно начать с <?= Html::a('главной', '/') ?>.
    </p>

</div>
