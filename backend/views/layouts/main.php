<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;

AppAsset::register($this);

rmrevin\yii\fontawesome\AssetBundle::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?= $this->registerLinkTag(['rel' => 'icon', 'type' => 'image/ico', 'href' => '/favicon.ico']) ?>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<!--
<?php
NavBar::begin([
    'brandLabel' => Yii::$app->name,
    'brandUrl' => Yii::$app->homeUrl,
    'options' => [
        'class' => 'navbar navbar-default navbar-fixed-top',
    ],
]);
$menuItems = [
    ['label' => 'Home', 'url' => ['/']],
];
if (Yii::$app->user->isGuest) {
    $menuItems[] = ['label' => 'Login', 'url' => ['/login']];
} else {
    $menuItems[] = '<li>'
        . Html::beginForm(['/logout'], 'post')
        . Html::submitButton(
            'Logout (' . Yii::$app->user->identity->username . ')',
            ['class' => 'btn btn-link logout']
        )
        . Html::endForm()
        . '</li>';
}
echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'items' => $menuItems,
]);
NavBar::end();
?>


<nav id="w0" class="navbar navbar-default navbar-fixed-top" role="navigation"><div class="container"><div class="navbar-header"><button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#w0-collapse"><span class="sr-only">Toggle navigation</span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span></button><a class="navbar-brand" href="/">Fresh office</a></div><div id="w0-collapse" class="collapse navbar-collapse"><ul id="w1" class="navbar-nav navbar-right nav"><li><a href="/">Home</a></li>
<li><form action="/logout" method="post">
        <input type="hidden" name="_csrf-backend" value="LW8zdjEtTWpVO1skBV8.LRUtVjRycmAaZV94IngcD1JiLXg4ZR0kIg=="><button type="submit" class="btn btn-link logout">Logout (root)</button></form></li></ul></div></div></nav>
-->

<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <?= Html::a(Yii::$app->name, '/', ['class' => 'navbar-brand']) ?>

        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><?= Html::a('Документы', ['/documents']) ?></li>
                <li><?= Html::beginForm(['/logout'], 'post')
                    . Html::submitButton('<i class="fa fa-power-off" aria-hidden="true"></i> Выход (' . Yii::$app->user->identity->username . ')', ['class' => 'btn btn-link logout'])
                    . Html::endForm() ?></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid main">
    <?= Breadcrumbs::widget([
        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
    ]) ?>

    <?= Alert::widget() ?>

    <?= $content ?>

</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
