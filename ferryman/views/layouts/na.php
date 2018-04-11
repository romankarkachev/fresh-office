<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;

ferryman\assets\AppAsset::register($this);

romankarkachev\coreui\CoreUIAsset::register($this);
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
<body class="app flex-row align-items-center">
<?php $this->beginBody() ?>
    <div class="container">
        <?= $content ?>

    </div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
