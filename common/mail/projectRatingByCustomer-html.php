<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $project_id integer идентификатор проекта */
/* @var $token string идентификатор инструмента для голосования */
?>
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="content-block">
            <strong>Уважаемый клиент!</strong>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Благодарим, что воспользовались нашими услугами по обращению с отходами. Пожалуйста, поставьте оценку качеству оказания услуг по последнему заказу.
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <!--[if lte IE 7]><style>#reviewStars-input{display:none}</style><![endif]-->

            <div id="reviewStars-input">
                <?= Html::a(Html::label(''), Yii::$app->urlManager->createAbsoluteUrl(['/customer/project-rating', 'token' => $token, 'rate' => 5]), ['title' => 'Ставлю наивысшую оценку']) ?>

                <?= Html::a(Html::label(''), Yii::$app->urlManager->createAbsoluteUrl(['/customer/project-rating', 'token' => $token, 'rate' => 4]), ['title' => 'Оцениваю работу хорошо']) ?>

                <?= Html::a(Html::label(''), Yii::$app->urlManager->createAbsoluteUrl(['/customer/project-rating', 'token' => $token, 'rate' => 3]), ['title' => 'Оцениваю работу удовлетворительно']) ?>

                <?= Html::a(Html::label(''), Yii::$app->urlManager->createAbsoluteUrl(['/customer/project-rating', 'token' => $token, 'rate' => 2]), ['title' => 'Оцениваю работу неудовлетворительно']) ?>

                <?= Html::a(Html::label(''), Yii::$app->urlManager->createAbsoluteUrl(['/customer/project-rating', 'token' => $token, 'rate' => 1]), ['title' => 'Над качеством оказания услуг необходимо серьезно поработать']) ?>

            </div>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            Нажмите <?= Html::a('здесь', 'http://31.148.13.223:8081/customer/project-rating?token=' . $token) ?>, если не отображаются пять звездочек для голосования.
        </td>
    </tr>
    <tr>
        <td class="content-block">Напоминаем, что свои заказы Вы можете смотреть в <?= Html::a('личном кабинете', 'http://31.148.13.223:8081/customer/login') ?>.</td>
    </tr>
</table>
