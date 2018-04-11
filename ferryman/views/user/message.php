<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $title string заголовок страницы */
/* @var $heading string заголовок блока */
/* @var $module dektrium\user\Module */
/* @var $force bool выводить принудительно */

$this->title = $title;
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mx-4">
                    <div class="card-body p-4">
                        <h1><?= $heading; ?></h1>
                        <?= $this->render('/_alert', ['module' => $module, 'force' => true]) ?>

                        <p class="text-muted mb-0">Это окно можно закрыть или <?= Html::a('перейти на главную', ['/']) ?>.</p>
                        <p class="text-muted mb-0">Вы также можете <?= Html::a('авторизоваться', ['/login']) ?>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
