<?php

use yii\helpers\Html;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\CEMessagesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */
/* @var $mailboxes array */

$this->title = 'Письма | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Письма';

$layout = '<div class="toolbar">
                    <!--<div class="btn-group">
                        <button type="button" class="btn btn-light">
                            <span class="fa fa-envelope"></span>
                        </button>
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
                    <button type="button" class="btn btn-light">
                        <span class="fa fa-trash-o"></span>
                    </button>-->
                    '. Html::a('<i class="fa fa-trash-o" aria-hidden="true"></i> Стереть все', ['clear'], ['class' => 'btn btn-danger', 'data' => [
                        'confirm' => 'Вы действительно хотите удалить все письма (из всех ящиков)?',
                        'method' => 'post',
                    ]]) . '
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
                    <div class="btn-group float-right">
                        {pager}
                    </div>
                </div><ul class="messages">{items}</ul>{pager}';
?>
<div class="mail">
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <div class="card">
        <div class="card-block">
            <?= Html::a('<i class="fa fa-times" aria-hidden="true"></i> Стереть вообще все', ['clear'], ['class' => 'btn btn-danger', 'data' => [
                'confirm' => 'Вы действительно хотите удалить все письма (из всех ящиков)?',
                'method' => 'post',
            ]]) ?>

            <?= Html::a('<i class="fa fa-cloud-download" aria-hidden="true"></i> Выгрузить вообще все', ['flush'], ['class' => 'btn btn-info']) ?>

        </div>
    </div>
    <div id="ui-view" class="animated fadeIn">
        <div class="email-app mb-4">
            <main class="inbox">
                <?= ListView::widget([
                    'dataProvider' => $dataProvider,
                    'pager' => [
                        'firstPageLabel' => 'Конец',
                        'lastPageLabel' => 'Начало',
                        'prevPageLabel' => '<span class="fa fa-chevron-left"></span>',
                        'nextPageLabel' => '<span class="fa fa-chevron-right"></span>',
                        'disabledPageCssClass' => 'page-item disabled',
                    ],
                    //'layout' => '{pager}<ul class="messages">{items}</ul>{pager}',
                    'layout' => $layout,
                    'itemView' => function ($model, $key, $index, $widget) {
                        /* @var $model \common\models\CEMessages */

                        return $this->render('_message', ['model' => $model]);
                    },
                ]); ?>

            </main>
        </div>
    </div>
</div>
