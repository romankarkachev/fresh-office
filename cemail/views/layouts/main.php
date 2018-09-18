<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use romankarkachev\coreui\widgets\Alert;
use cemail\widgets\Sidebar;
use romankarkachev\coreui\widgets\Breadcrumbs;

\cemail\assets\AppAsset::register($this);

\romankarkachev\coreui\CoreUIAsset::register($this);

\hiqdev\assets\icheck\iCheckAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?= $this->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => Url::to(['/images/favicon.png'])]) ?>
    <?= $this->registerLinkTag(['rel' => 'icon', 'type' => 'image/ico', 'href' => Url::to(['/images/favicon.ico'])]) ?>
    <?php $this->head() ?>
</head>
<body class="app header-fixed sidebar-fixed">
<?php $this->beginBody() ?>
    <header class="app-header navbar">
        <button class="navbar-toggler mobile-sidebar-toggler d-lg-none" type="button"><i class="fa fa-bars" aria-hidden="true"></i></button>
        <?= Html::a('', ['/'], ['class' => 'navbar-brand']) ?>

        <ul class="nav navbar-nav d-md-down-none">
            <li class="nav-item">
                <?= Html::a('<i class="fa fa-bars" aria-hidden="true"></i>', '#', ['class' => 'nav-link navbar-toggler sidebar-toggler', 'title' => 'Показать/скрыть меню']) ?>

            </li>
        </ul>
        <ul class="nav navbar-nav ml-auto">
            <li class="nav-item">
                <?= Html::a('<i class="icon-logout"></i>', ['/logout'], ['class' => 'nav-link', 'title' => 'Выйти из системы', 'data-method' => 'post']) ?>

            </li>
        </ul>
    </header>
    <div class="app-body">
        <div class="sidebar">
            <nav class="sidebar-nav">
                <?= Sidebar::widget([
                    'options' => ['id' => 'side-menu', 'class' => 'nav'],
                    'encodeLabels' => false,
                    'items' => \common\models\User::prepateSidebarMenuForCemail(),
                ]) ?>

            </nav>
        </div>
        <main class="main">
            <?= Breadcrumbs::widget([
                'homeLink' => [
                    'label' => '<i class="fa fa-home"></i>',
                    'url' => Yii::$app->homeUrl,
                    'title' => 'Главная',
                ],
                'encodeLabels' => false,
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                'linksAtRight' => isset($this->params['breadcrumbsRight']) ? $this->params['breadcrumbsRight'] : [],
            ]) ?>

            <div class="container-fluid">
                <?= Alert::widget() ?>

                <?= $content ?>

            </div>
        </main>
    </div>
    <footer class="app-footer">
        &copy; <?= date('Y') ?> <?= Html::a(Yii::$app->name, ['/']) ?>

        <span class="float-right">Вы авторизованы как <?= Yii::$app->user->identity->profile->name == null || Yii::$app->user->identity->profile->name == '' ? '' : Yii::$app->user->identity->profile->name ?>.</span>
    </footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>