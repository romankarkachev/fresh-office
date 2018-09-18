<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use romankarkachev\coreui\widgets\Alert;
use romankarkachev\coreui\widgets\Sidebar;
use romankarkachev\coreui\widgets\Breadcrumbs;

customer\assets\AppAsset::register($this);

\romankarkachev\coreui\CoreUIAsset::register($this);

\hiqdev\assets\icheck\iCheckAsset::register($this);

$items = [
    ['label' => 'Рабочий стол', 'icon' => 'fa fa-desktop', 'url' => ['/']],
    ['label' => 'Заказы', 'icon' => 'fa fa-list-ol', 'url' => ['/orders']],
];
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
                <a class="nav-link navbar-toggler sidebar-toggler" href="#"><i class="fa fa-bars" aria-hidden="true"></i></a>
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
                    'items' => $items,
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
﻿<!--BEGIN FRESHOFFICE CHAT CODE-->
<script type='text/javascript'src="http://ecosystemstoragewe.blob.core.windows.net/cdn/sourcebuster.js"></script><script type='text/javascript'>var sbjs=_sbjs||{session_length:15,timezone_offset:3};if(typeof sbjs!=='undefined')sbjs.init(_sbjs);!function(){function e(){var frame=document.createElement("iframe"),_div=document.createElement("div");_frame.src="http://ecosystem-CDN-endpoint1.azureedge.net/chat-utf8-http.html?subs_id=2202&color=";_frame.title="",_frame.role="presentation",_frame.setAttribute("name","freshoffice-chat");_frame.setAttribute("id","iframe-freshoffice-chat-container");_frame.setAttribute("frameborder","no");(_frame.frameElement||_frame).style.cssText="width:100%;height:100%;border:0;padding:0;marging:0";_div.setAttribute("id","div-freshoffice-char-container");_div.style.cssText="visibility: visible;width: 78px;height: 78px;display: block;right: 0px;bottom: 0px;position: fixed;z-index:10000000!important";_div.appendChild(_frame);var e=document.body.lastChild;e?e.parentNode.insertBefore(_div,e.nextSibling):document.body.appendChild(_div);window.addEventListener("message",function(e){if(e.data.indexOf("change-chat-size")>=0){var t=JSON.parse(e.data),i=document.getElementById("div-freshoffice-char-container");i.style.height=t.height+(""!=t.height&&"auintialto"!=t.height?"px":"");i.style.width=t.width+(""!=t.width&&"auto"!=t.width?"px":"");i.style.top=t.top}else if(e.data.indexOf("get-source")>=0){if(typeof sbjs!=='undefined'&&sbjs.get){var get=sbjs.get;var data={current:_get.current,first:_get.first,session:_get.session};document.getElementById('iframe-freshoffice-chat-container').contentWindow.postMessage(JSON.stringify(_data),"*")}else document.getElementById('iframe-freshoffice-chat-container').contentWindow.postMessage('',"*")}})}var t=document,i=window;"complete"==t.readyState?e():i.attachEvent?i.attachEvent("onload",e):i.addEventListener("load",e,false)}();</script>
<!--END FRESHOFFICE CHAT CODE-->
</body>
</html>
<?php $this->endPage() ?>
