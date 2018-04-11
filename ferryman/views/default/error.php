<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="clearfix">
                <h1 class="float-left display-3 mr-4"><?= $exception->statusCode ?></h1>
                <h4 class="pt-3"><?= $message ?></h4>
                <p class="text-muted">Произошла ошибка, которую Вы можете видеть.</p>
            </div>
            <?= Html::a('На главную', ['/'], ['class' => 'btn btn-info']) ?>

        </div>
    </div>
</div>
