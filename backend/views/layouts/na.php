<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;

backend\assets\UnauthorizedAsset::register($this);
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
    <div class="container">
        <?= Alert::widget() ?>

        <?= $content ?>

    </div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
