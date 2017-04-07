<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use backend\assets\AppAsset;
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

<?php
NavBar::begin([
    'brandLabel' => Yii::$app->name,
    'brandUrl' => '/',
    'options' => ['class' => 'navbar navbar-default navbar-fixed-top'],
    'innerContainerOptions' => ['class' => 'container-fluid'],
]);
$items = [];
if (Yii::$app->user->can('root'))
    $items = [
        ['label' => '<i class="fa fa-file-text-o fa-lg"></i>', 'url' => ['/documents'], 'linkOptions' => ['title' => 'Документы']],
        ['label' => '<i class="fa fa-volume-control-phone fa-lg"></i>', 'url' => ['/appeals'], 'linkOptions' => ['title' => 'Обращения']],
        [
            'label' => 'Справочники',
            'url' => '#',
            'items' => [
                ['label' => '<i class="fa fa-cubes text-info"></i> Номенклатура', 'url' => ['/products']],
                ['label' => '<i class="fa fa-recycle text-info"></i> Виды обращения', 'url' => ['/handling-kinds']],
                ['label' => '<i class="fa fa-remove text-info"></i> Исключения номенклатуры', 'url' => ['/products-excludes']],
                ['label' => '<i class="fa fa-user-circle-o text-info"></i> Ответственные лица', 'url' => ['/responsible']],
                '<li class="divider"></li>',
                ['label' => '<i class="fa fa-users text-info"></i> Пользователи', 'url' => ['/users']],
            ],
        ],
        [
            'label' => 'Отчеты',
            'url' => '#',
            'items' => [
                ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по клиентам', 'url' => ['/reports/eins']],
            ],
        ],
    ];
elseif (Yii::$app->user->can('role_documents'))
    $items = [
        ['label' => '<i class="fa fa-file-text-o fa-lg"></i>', 'url' => ['/documents'], 'linkOptions' => ['title' => 'Документы']],
    ];
elseif (Yii::$app->user->can('role_report1'))
    $items = [
        ['label' => '<i class="fa fa-pie-chart text-success"></i> Отчет по клиентам', 'url' => ['/reports/eins']],
    ];
$items[] = '<li>'
    . Html::beginForm(['/logout'], 'post')
    . Html::submitButton(
        '<i class="fa fa-power-off" aria-hidden="true"></i> Выход (' . Yii::$app->user->identity->username . ')',
        ['class' => 'btn btn-link logout']
    )
    . Html::endForm()
    . '</li>';
echo Nav::widget([
    'options' => ['class' => 'nav navbar-nav navbar-right'],
    'items' => $items,
    'encodeLabels' => false,
]);
NavBar::end();
?>

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
