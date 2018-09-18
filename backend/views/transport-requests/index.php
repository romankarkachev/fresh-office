<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\grid\GridView;
use common\models\TransportRequestsStates;

/* @var $this yii\web\View */
/* @var $searchModel common\models\TransportRequestsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */

$this->title = 'Запросы на транспорт | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Запросы на транспорт';

$favoriteGs = '/images/favorite16gs.png';
$favorite = '/images/favorite16.png';
?>
<div class="transport-requests-list">
    <?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

    <p>
        <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['create'], ['class' => 'btn btn-success']) ?>

        <?php if (Yii::$app->user->identity->username == 'administrator'): ?>
        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

        <?php endif; ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'customer_name',
            [
                'attribute' => 'stateName',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\TransportRequests */
                    // возможность вернуть в обработку
                    $returnToProcess = Html::a('<p><i class="fa fa-refresh" aria-hidden="true"></i></p>', '#', ['id' => 'returnToProcess' . $model->id, 'data-id' => $model->id, 'title' => 'Вернуть в обработку']);

                    $newMessages = '';
                    if ($model->unreadMessagesCount > 0)
                        $newMessages = '<p>' . Html::a('<i class="fa fa-commenting text-primary" aria-hidden="true"></i>', ['update', 'id' => $model->id, '#' => 'messages'], ['title' => 'Новых сообщений: ' . $model->unreadMessagesCount]) . '</p>';

                    $newPrivateMessages = '';
                    if ($model->unreadPrivateMessagesCount > 0)
                        $newPrivateMessages = '<p>' . Html::a('<i class="fa fa-comments text-warning" aria-hidden="true"></i>', ['update', 'id' => $model->id, '#' => 'privatemessages'], ['title' => 'Новые приватные сообщения: ' . $model->unreadPrivateMessagesCount]) . '</p>';

                    if ($model->state_id == TransportRequestsStates::STATE_ЗАКРЫТ)
                        return '<i class="fa fa-check-square-o text-success" aria-hidden="true"></i>' . $returnToProcess . $newMessages . $newPrivateMessages;

                    if ($model->state_id == TransportRequestsStates::STATE_НОВЫЙ)
                        return '<strong>' . $model->{$column->attribute} . '</strong>' . $newMessages . $newPrivateMessages;

                    return $model->{$column->attribute} . $newMessages . $newPrivateMessages;
                },
                'options' => ['width' => '100'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Создан',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\TransportRequests */
                    return Yii::$app->formatter->asDate($model->{$column->attribute}, 'php:d.m.Y в H:i');
                },
                'options' => ['width' => '150'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            'createdByName',
            [
                'attribute' => 'regionName',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\TransportRequests */

                    return $model->regionName . ' ' . $model->cityName;
                },
            ],
            'periodicityName',
            [
                'attribute' => 'tpWasteLinear',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\TransportRequests */

                    return nl2br($model->{$column->attribute});
                },
            ],
            [
                'attribute' => 'tpTransportLinear',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\TransportRequests */

                    return nl2br($model->{$column->attribute});
                },
                'options' => ['width' => '300'],
            ],
            //'unreadMessagesCount',
            //'finished_at',
            //'customer_id',
            // 'city_id',
            // 'address',
            // 'comment_manager:ntext',
            // 'comment_logist:ntext',
            // 'our_loading',
            //
            // 'special_conditions:ntext',
            // 'spec_free',
            // 'spec_hose',
            // 'spec_cond:ntext',
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{update} {meignore} {favorite} {delete}',
                'buttons' => [
                    'meignore' => function ($url, $model) {
                        /* @var $model \common\models\TransportRequests */
                        return Html::a(Html::img('/images/email-warning16.png'), '#', ['title' => Yii::t('yii', 'Запрос продолжительное время игнорируют'), 'class' => 'btn btn-xs btn-default', 'id' => 'btnMeignored' . $model->id, 'data-id' => $model->id]);
                    },
                    'favorite' => function ($url, $model) {
                        /* @var $model \common\models\TransportRequests */
                        $favorite = '/images/favorite16gs.png';
                        if ($model->is_favorite) $favorite = '/images/favorite16.png';

                        return Html::a(Html::img($favorite), '#', ['title' => Yii::t('yii', 'Добавить (удалить) в Избранное'), 'class' => 'btn btn-xs btn-default', 'id' => 'btnFavorites' . $model->id, 'data-id' => $model->id]);
                    },
                    'update' => function ($url, $model) {
                        return Html::a('<i class="fa fa-pencil"></i>', $url, ['title' => Yii::t('yii', 'Редактировать'), 'class' => 'btn btn-xs btn-default']);
                    },
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-trash-o"></i>', $url, ['title' => Yii::t('yii', 'Удалить'), 'class' => 'btn btn-xs btn-danger', 'aria-label' => Yii::t('yii', 'Delete'), 'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'data-method' => 'post', 'data-pjax' => '0',]);
                    }
                ],
                'options' => ['width' => '130'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

</div>
<?php
$url = Url::to(['/transport-requests/toggle-favorite']);
$url_meigonred = Url::to(['/transport-requests/meignored']);
$url_return_to_process = Url::to(['/transport-requests/return-to-process']);

$this->registerJs(<<<JS
// Функция-обработчик изменения даты в любом из соответствующих полей.
//
function anyDateOnChange() {
    //\$button = $("button[type='submit']");
    \$button = $("#btnSearch");
    \$button.attr("disabled", "disabled");
    text = \$button.text();
    \$button.text("Подождите...");
    setTimeout(function () {
        \$button.removeAttr("disabled");
        \$button.text(text);
    }, 1500);
}
JS
, \yii\web\View::POS_BEGIN);

$this->registerJs(<<<JS
$("input").iCheck({
    checkboxClass: 'icheckbox_square-green',
});

// Обработчик щелчка по кнопке "Избранный". Выполняет переключение этого признака.
//
function btnFavoriteOnClick() {
    \$btn = $(this);
    \$btn.html("<i class=\"fa fa-cog fa-spin fa-2x text-muted\"></i>");
    id = \$btn.attr("data-id");
    if (id != "" && id != undefined)
        $.get("$url?id=" + id, function(data) {
            if (data == true)
                \$btn.html("<img src=\"$favorite\" />");
            else
                \$btn.html("<img src=\"$favoriteGs\" />");
        });

    return false;
} // btnFavoriteOnClick()

// Обработчик щелчка по кнопке "Меня игнорируют". Выполняет отправку письма руководству с мольбой о помощи в
// реакции на запрос.
//
function btnMeignoredOnClick() {
    \$btn = $(this);
    \$btn.html("<i class=\"fa fa-cog fa-spin text-muted\"></i>");
    id = \$btn.attr("data-id");
    $.get("$url_meigonred?id=" + id, function(data) {
        if (data == true)
            \$btn.html('<i class="fa fa-check-circle-o text-success"></i>');
        else
            \$btn.html('<i class="fa fa-times text-danger"></i>');
    });

    return false;
} // btnMeignoredOnClick()

// Обработчик щелчка по ссылке "Вернуть в обработку".
//
function returnToProcessOnClick() {
    id = $(this).attr("data-id");
    if (id != "" && confirm("Вы действительно хотите вернуть запрос № " + id + " в обработку?")) {
        icon = $(this).children().children();
        if (icon != undefined) icon.addClass("fa-spin");

        $.get("$url_return_to_process?id=" + id, function(data) {
            if (data == true)
                icon.removeClass("fa-refresh").addClass("fa-check-circle-o text-success");
            else
                icon.removeClass("fa-refresh").addClass("fa-times text-danger");
    
            if (icon != undefined) icon.removeClass("fa-spin");
        });
    }

    return false;
} // returnToProcessOnClick()

$(document).on("click", "a[id ^= 'btnFavorite']", btnFavoriteOnClick);
$(document).on("click", "a[id ^= 'btnMeignored']", btnMeignoredOnClick);
$(document).on("click", "a[id ^= 'returnToProcess']", returnToProcessOnClick);
JS
, \yii\web\View::POS_READY);
?>
