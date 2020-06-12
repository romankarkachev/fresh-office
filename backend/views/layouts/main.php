<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use backend\assets\AppAsset;
use common\widgets\Alert;

use common\models\MainMenu;
use backend\controllers\AdvanceHoldersController;
use backend\controllers\AdvanceReportsController;

AppAsset::register($this);

rmrevin\yii\fontawesome\AssetBundle::register($this);

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
    <?= $this->registerLinkTag(['rel' => 'icon', 'type' => 'image/ico', 'href' => '/favicon.ico']) ?>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?php
NavBar::begin([
    'brandLabel' => Yii::$app->name,
    'brandUrl' => '/',
    'options' => ['id' => 'navbar', 'class' => 'navbar navbar-default navbar-fixed-top'],
    'innerContainerOptions' => ['class' => 'container-fluid'],
]);

// пункты главного меню
$items = MainMenu::getItems(Yii::$app->user->identity->getRoleName());

// из меню старших операторов убираем пункт "Телефония", оставляем только для одного пользователя - Лукониной
if (Yii::$app->user->can('operator_head') && Yii::$app->user->id != 38) {
    unset($items[0][3]);
}

// баланс подотчетников
if (!Yii::$app->user->can('root') && !Yii::$app->user->can('accountant_b') && !Yii::$app->user->isGuest) {
    $balance = Yii::$app->user->identity->advanceBalanceStored;
    if (false !== $balance) {
        $options = null;
        if ($balance > 0) {
            $options = ['class' => 'bg-danger'];
        }

        $subItems = [
            ['label' => 'Ведомость взаиморасчетов', 'url' => AdvanceHoldersController::ROOT_URL_AS_ARRAY],
            ['label' => 'Создать авансовый отчет', 'url' => AdvanceReportsController::URL_NEW_AS_ARRAY],
            ['label' => AdvanceReportsController::MAIN_MENU_LABEL, 'url' => AdvanceReportsController::URL_ROOT_AS_ARRAY],
        ];
        if (Yii::$app->user->identity->profile->can_fod) {
            $subItems[] = ['label' => AdvanceHoldersController::URL_DELEGATION_LABEL, 'url' => AdvanceHoldersController::URL_DELEGATION_AS_ARRAY];
        }

        $items[] = [
            'label' => common\models\FinanceTransactions::getPrettyAmount($balance) . '  &#8381;',
            'linkOptions' => [
                'title' => 'Сумма взаиморасчетов по подотчету',
                'class' => 'text-bold',
            ],
            'options' => $options,
            'url' => '#',
            'items' => $subItems,
        ];
    }
}

if (!Yii::$app->user->isGuest) {
    $items[] = '<li>'
        . Html::beginForm(['/logout'], 'post')
        . Html::submitButton(
            '<i class="fa fa-power-off" aria-hidden="true"></i> Выход (' . Yii::$app->user->identity->username . ')',
            ['class' => 'btn btn-link logout']
        )
        . Html::endForm()
        . '</li>';
}

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
        'encodeLabels' => false,
    ]) ?>

    <?= Alert::widget() ?>

    <?= $content ?>

</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
