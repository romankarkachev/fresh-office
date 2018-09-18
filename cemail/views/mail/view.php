<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use common\models\CEMessagesSearch;

/* @var $this yii\web\View */
/* @var $model common\models\CEMessages */

$this->title = $model->fromRepForTitle . HtmlPurifier::process(' &mdash; Письма | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Письма', 'url' => ['/mail']];
$this->params['breadcrumbs'][] = ['label' => $model->mailbox->name, 'url' => ['/mail', (new CEMessagesSearch())->formName() => [
    'mailbox_id' => $model->mailbox->id,
]]];
$this->params['breadcrumbs'][] = $model->fromRep;

$attachedFiles = $model->attachedFiles;
?>
<div class="cemessages-view">
    <div class="email-app mb-4">
        <main class="message">
            <div class="toolbar">
                <div class="btn-group">
                    <button type="button" class="btn btn-light">
                        <span class="fa fa-star"></span>
                    </button>
                    <button type="button" class="btn btn-light">
                        <span class="fa fa-star-o"></span>
                    </button>
                    <button type="button" class="btn btn-light">
                        <span class="fa fa-bookmark-o"></span>
                    </button>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-light">
                        <span class="fa fa-mail-reply"></span>
                    </button>
                    <button type="button" class="btn btn-light">
                        <span class="fa fa-mail-reply-all"></span>
                    </button>
                    <button type="button" class="btn btn-light">
                        <span class="fa fa-mail-forward"></span>
                    </button>
                </div>
                <?= Html::a('<i class="fa fa-trash-o"></i>', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Are you sure you want to delete this item?',
                        'method' => 'post',
                    ],
                ]) ?>

                <div class="btn-group">
                    <button type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown">
                        <span class="fa fa-tags"></span>
                        <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#">add label
                            <span class="badge badge-danger"> Home</span>
                        </a>
                        <a class="dropdown-item" href="#">add label
                            <span class="badge badge-info"> Job</span>
                        </a>
                        <a class="dropdown-item" href="#">add label
                            <span class="badge badge-success"> Clients</span>
                        </a>
                        <a class="dropdown-item" href="#">add label
                            <span class="badge badge-warning"> News</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="details">
                <div class="title"><?= $model->subject ?></div>
                <div class="header">
                    <i class="fa fa-user fa-3x avatar"></i>
                    <div class="from">
                        <span><?= $model->fromName ?></span>
                        <?= $model->fromEmail ?>
                    </div>
                    <div class="date"><?= Yii::$app->formatter->asDate($model->created_at, 'php:d F Y') ?>,
                        <b><?= Yii::$app->formatter->asDate($model->created_at, 'php:H:i') ?></b>
                    </div>
                </div>
                <div class="content">
                    <blockquote>
                        <?= empty($model->body_html) ? nl2br($model->body_text) : $model->body_html ?>

                    </blockquote>
                </div>
                <?php if (count($attachedFiles) > 0): ?>
                <div class="attachments">
                    <?php foreach ($attachedFiles as $attachedFile): ?>
                    <div class="attachment">
                        <span class="badge badge-danger">zip</span>
                        <b><?= $attachedFile->ofn ?></b>
                        <i>(<?= Yii::$app->formatter->asShortSize($attachedFile->size, 2) ?>)</i>
                        <span class="menu">
                            <a href="#" class="fa fa-search"></a>
                            <a href="#" class="fa fa-share"></a>
                            <a href="#" class="fa fa-cloud-download"></a>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <!--
                <form method="post" action="">
                    <div class="form-group">
                        <textarea class="form-control" id="message" name="body" rows="12" placeholder="Click here to reply"></textarea>
                    </div>
                    <div class="form-group">
                        <button tabindex="3" type="submit" class="btn btn-success">Send message</button>
                    </div>
                </form>
                -->
            </div>
        </main>
    </div>
</div>
