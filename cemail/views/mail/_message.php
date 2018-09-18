<?php

/* @var $this yii\web\View */
/* @var $model \common\models\CEMessages */
?>

<li class="message">
                        <a href="<?= \yii\helpers\Url::to(['/mail/view/' . $model->id]) ?>">
                            <div class="actions">
                                <span class="action"><i class="fa fa-square-o"></i></span>
                                <span class="action"><i class="fa fa-star-o"></i></span>
                            </div>
                            <div class="header">
                                <span class="from"><?= $model->fromHtmlRep ?></span>
                                <span class="date">
                                    <span class="badge badge-danger mr-1"><?= $model->mailbox->name ?></span>
                                    <span class="badge badge-info mr-1"><?= str_replace('|', ' / ', $model->folder_name) ?></span>
                                    <span class="fa fa-paper-clip"></span> <?= Yii::$app->formatter->asDate($model->created_at, 'php:d F Y H:i') ?>

                                </span>
                            </div>
                            <div class="title">
                                <?= $model->subject ?>

                            </div>
                            <div class="description">
                                <?= $model->body_text ?>

                            </div>
                        </a>
                    </li>
