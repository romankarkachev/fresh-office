<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\grid\GridView;
use yii\web\View;
use common\models\pbxCalls;
use common\models\pbxCallsSearch;
use \backend\controllers\PbxCallsController;

/* @var $this yii\web\View */
/* @var $searchModel common\models\pbxCallsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $predefinedPeriods array доступные предустановленные периоды для отбора по ним */
/* @var $callsDirections array доступные направления звонков для отбора по ним */
/* @var $isNewVariations array доступные варианты для отбора по полю "Новый" */

$this->title = 'Телефония | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Телефония';

$ppToday = pbxCallsSearch::FILTER_PREDEFINED_PERIOD_TODAY;
$ppYesterday = pbxCallsSearch::FILTER_PREDEFINED_PERIOD_YESTERDAY;
$ppWeek = pbxCallsSearch::FILTER_PREDEFINED_PERIOD_WEEK;
$ppMonth = pbxCallsSearch::FILTER_PREDEFINED_PERIOD_MONTH;

$lblWindowHeaderComments = 'Комментарии к записи разговора';
$lblWindowHeaderIdentifyCounteragent = 'Идентификация контрагента';
$lblButtonSumbitIdentification = \backend\models\pbxIdentifyCounteragentForm::BUTTON_SUBMIT_IDENTIFICATION_LABEL;
$btnTransribeFewId = 'btnTranscribeFew';
$btnTransribeFewPrompt = 'Распознать выделенные';
$gwConversationsId = 'gw-conversations';
$preloader = '<i class="fa fa-spinner fa-pulse fa-fw text-primary"></i><span class="sr-only">Подождите...</span>';

$btnTransribeFew = '';
if ($dataProvider->getTotalCount() > 0) {
    $btnTransribeFew = '<div class="col-md-6">' . Html::a($btnTransribeFewPrompt, '#', ['id' => $btnTransribeFewId, 'class' => 'btn btn-default btn-xs pull-left', 'title' => 'Поставить в очередь на распознание выделенные файлы']) . '</div>';
}
?>
<div class="pbx-calls-list">
    <?= $this->render('_search', [
        'model' => $searchModel,
        'callsDirections' => $callsDirections,
        'isNewVariations' => $isNewVariations,
        'predefinedPeriods' => $predefinedPeriods,
    ]); ?>

    <?= GridView::widget([
        'id' => $gwConversationsId,
        'layout' => "<div style=\"position: relative; min-height: 20px;\"><small class=\"pull-right form-text text-muted\" style=\"position: absolute; bottom: 0; right: 0;\">{summary}</small></div>\n{items}\n<div class=\"row\">$btnTransribeFew<div class=\"col-md-6\"><small class=\"pull-right form-text text-muted\">{summary}</small></div></div>\n{pager}",
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'options' => ['width' => '30'],
            ],
            [
                'attribute' => 'calldate',
                'label' => 'Дата',
                'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
            [
                'label' => 'Статус',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\pbxCalls */
                    /* @var $column \yii\grid\DataColumn */

                    return $model->stateName;
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => function ($model, $key, $index, $gridView) {
                    /* @var $model \common\models\pbxCalls */

                    return ['class' => 'text-center' . $model->stateElementClass];
                },
                'options' => ['width' => '110'],
            ],
            [
                'attribute' => 'src',
                'label' => 'Абонент',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\pbxCalls */
                    /* @var $column \yii\grid\DataColumn */

                    $result = $model->{$column->attribute};

                    if (strlen($result) > 1 && strlen($result) <= 4) {
                        $employeeName = $model->srcEmployeeName;
                        if (!empty($employeeName)) {
                            $result .= ' <small class="text-muted">' . $employeeName . '</small>';
                        }
                        else {
                            $result = Html::a($result . ' <i class="fa fa-plus-circle"></i>', ['/pbx-calls/create-internal-number', 'id' => $model->id, 'field' => $column->attribute], ['target' => '_blank', 'title' => 'Нажмите, чтобы привязать внутренний номер (откроется в новом окне)', 'data-pjax' => '0']);
                        }
                    }
                    else {
                        $btnId = 'btnIdentifyCounteragent' . $model->id;
                        switch (true) {
                            case ($model->fo_ca_id === pbxCalls::ПРИЗНАК_КОНТРАГЕНТ_ИДЕНТИФИЦИРОВАН_НЕОДНОЗНАЧНО):
                                // неоднозначный контрагент
                                $result = $result . ' ' . Html::a('<i class="fa fa-ellipsis-h"></i>', '#', [
                                    'id' => $btnId,
                                    'data-id' => $model->id,
                                    'data-phone' => $model->src,
                                    'class' => 'text-success',
                                    'target' => '_blank',
                                    'title' => 'Нажмите, чтобы привязать номер к неоднозначно идентифицированному контрагенту, которого Вы укажете (откроется в модальном окне)',
                                    'data-pjax' => '0'
                                ]);
                                break;
                            case ($model->fo_ca_id == pbxCalls::ПРИЗНАК_КОНТРАГЕНТ_ВООБЩЕ_НЕ_ИДЕНТИФИЦИРОВАН):
                                // контрагент вообще не идентифицирован
                                $result = $result . ' ' . Html::a('<i class="fa fa-plus-circle"></i>', '#', [
                                    'id' => $btnId,
                                    'data-id' => $model->id,
                                    'data-phone' => $model->src,
                                    'class' => 'text-success',
                                    'target' => '_blank',
                                    'title' => 'Нажмите, чтобы привязать номер к контрагенту, которого Вы выберете (откроется в модальном окне)',
                                    'data-pjax' => '0'
                                ]);
                                break;
                            case ($model->fo_ca_id > 0):
                                $result .= ' <span id="focaName' . $model->id . '" class="sr-only" data-focaid="' . $model->fo_ca_id . '">' . $model->fo_ca_id . '</span>';
                                break;
                        }
                    }

                    return $result;
                },
            ],
            [
                'attribute' => 'dst',
                'label' => 'Адресат',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\pbxCalls */
                    /* @var $column \yii\grid\DataColumn */

                    $result = $model->{$column->attribute};
                    $employeeName = $model->dstEmployeeName;
                    if (!empty($employeeName)) {
                        $result .= ' <small class="text-muted">' . $employeeName . '</small>';
                    }
                    elseif (strlen($result) > 1 && strlen($result) <= 4) {
                        $result = Html::a($result . ' <i class="fa fa-plus-circle"></i>', ['/pbx-calls/create-internal-number', 'id' => $model->id, 'field' => $column->attribute], ['target' => '_blank', 'title' => 'Нажмите, чтобы привязать внутренний номер (откроется в новом окне)', 'data-pjax' => '0']);
                    }

                    return $result;
                },
            ],
            //'clid',
            [
                'attribute' => 'regionName',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\pbxCalls */
                    /* @var $column \yii\grid\DataColumn */

                    $array = explode('|', $model->{$column->attribute});
                    if (is_array($array) && count($array) == 2) {
                        return trim($array[0]) . ' <small class="text-muted">' . $array[1] . '</small>';
                    }
                    else return $model->{$column->attribute};
                },
            ],
            'websiteName',
            [
                'attribute' => 'billsec',
                'label' => 'Длительность',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    /* @var $model \common\models\pbxCalls */
                    /* @var $column \yii\grid\DataColumn */

                    $duration = pbxCalls::formatConversationDuration($model->{$column->attribute});
                    if ($duration != '-') {
                        $buttons = Html::a(
                            Html::tag('i', '', ['class' => 'fa fa-play-circle-o text-primary', 'aria-hidden' => 'true']) . ' ' . $duration,
                            Url::to(['/pbx-calls/preview-file', 'id' => $model->id]),
                            ['id' => 'btnPlay' . $model->id, 'class' => 'btn btn-default btn-xs', 'title' => 'Воспроизвести эту запись разговора']);

                        // кнопка "Скачать результаты распознавания разговора"
                        if (!empty($model->recognitionFfp)) {
                            $buttons .= ' ' . Html::a(Html::tag('i', '', ['class' => 'fa fa-cloud-download', 'aria-hidden' => true]), Url::to(\yii\helpers\ArrayHelper::merge(PbxCallsController::URL_DOWNLOAD_RECOGNITION_RESULT_AS_ARRAY, ['id' => $model->id])), ['class' => 'btn btn-warning btn-xs', 'title' => 'Скачать результаты распознавания разговора']);
                        }

                        return $buttons;
                    }
                    else
                        return $duration;
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '110'],
            ],
            [
                'attribute' => 'fo_ca_id',
                'options' => ['width' => '80'],
                'visible' => false,
            ],
            [
                'label' => 'Действия',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\pbxCalls */
                    /* @var $column \yii\grid\DataColumn */

                    $buttons = '';

                    // кнопка "Отметить как новый"
                    if ($model->is_new) {
                        $class = 'success';
                        $label = Html::tag('i', '', ['class' => 'fa fa-asterisk', 'aria-hidden' => true]);
                        $title = 'Звонок помечен как от нового контрагента. Щелкните, чтобы снять пометку';
                    }
                    else {
                        $class = 'default';
                        $label = 'Новый';
                        $title = 'Нажмите, чтобы отметить этот звонок как от нового контрагента';
                    }

                    $buttons .= ' ' . Html::a($label, '#', ['id' => 'btnToggleNew' . $model->id, 'data-id' => $model->id, 'class' => 'btn btn-' . $class . ' btn-xs', 'title' => $title]);

                    // кнопка "Вставить комментарий"
                    $buttons .= ' ' . Html::a(Html::tag('i', '', ['class' => 'fa fa-comments', 'aria-hidden' => true]), '#', ['id' => 'btnShowComments' . $model->id, 'data-id' => $model->id, 'class' => 'btn btn-default btn-xs', 'title' => '']);

                    // кнопка "Скачать запись разговора"
                    $buttons .= ' ' . Html::a(Html::tag('i', '', ['class' => 'fa fa-cloud-download', 'aria-hidden' => true]), Url::to(['/pbx-calls/preview-file', 'id' => $model->id]), ['class' => 'btn btn-default btn-xs', 'title' => 'Скачать запись разговора']);

                    return $buttons;
                },
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
                'options' => ['width' => '130'],
            ],
        ],
    ]); ?>

</div>
<div id="mvPhoneConversation" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div id="modal_container" class="modal-dialog modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Запись разговора</h4>
            </div>
            <div class="modal-body">
                <?= $this->render('_conversation_play_form'); ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<div id="modalWindow" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div id="modal_container" class="modal-dialog modal-info modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header"><h4 id="modal_title" class="modal-title"><?= $lblWindowHeaderComments; ?></h4></div>
            <div id="modal_body" class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<div class="global-preloader"></div>
<?php
$this->registerCss('
.global-preloader {
    display:    none;
    position:   fixed;
    z-index:    1000;
    top:        0;
    left:       0;
    height:     100%;
    width:      100%;
    background: rgba( 255, 255, 255, .8 )
    url("/images/preloader96.gif")
    50% 50%
    no-repeat;
}

body.loading {
    overflow: hidden;
}

body.loading .global-preloader {
    display: block;
}
');

$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js', ['depends' => 'yii\web\JqueryAsset', 'position' => View::POS_END]);

$urlRenderPlayConversationForm = Url::to(['/pbx-calls/render-play-conversation-form']);
$urlShowComments = Url::to(['/pbx-calls/show-comments']);
$urlToggleNew = Url::to(['/pbx-calls/toggle-new']);
$urlGetCounteragentsName = Url::to(['/pbx-calls/get-counteragents-name']);
$urlFilterByFoCaId = Url::to(['/pbx-calls', $searchModel->formName() => ['fo_ca_id' => '']]);
$urlIdentifyFoCa = Url::to(['/pbx-calls/identify-counteragent-form']);
$urlApplyCounteragentsIdentification = Url::to(['/pbx-calls/apply-identification']);
$urlTranscribeSelected = Url::to(PbxCallsController::URL_TRANSCRIBE_SELECTED_FILES_AS_ARRAY);

$this->registerJs(<<<JS
// Функция-обработчик изменения даты в любом из соответствующих полей.
//
function anyDateOnChange() {
    \$button = $("#btnSearch");
    \$button.attr("disabled", "disabled");
    text = \$button.html();
    \$button.text("Подождите...");
    setTimeout(function () {
        \$button.removeAttr("disabled");
        \$button.html(text);
    }, 1500);
}
JS
, View::POS_BEGIN);

$this->registerJs(<<<JS

var checkedFiles = false;
$("input[type='checkbox']").iCheck({checkboxClass: "icheckbox_square-green"});

// После загрузки страницы проходим по всем элементам, где идентифицирован контрагент и запрашиваем их наименования
//
function faceCounteragents() {
    var focas = [];

    $("span[id^='focaName']").each(function(index) {
        var fo_ca_id = $(this).attr("data-focaid");
        if ($.inArray(fo_ca_id, focas) === -1) focas.push(fo_ca_id);
    });
    $.each(focas, function(index, element) {
        $("span[data-focaid='" + element + "']").html('$preloader').removeClass("sr-only");
        $.get("$urlGetCounteragentsName?id=" + element, function(result) {
            if (result != false) {
                $("span[data-focaid='" + element + "']").replaceWith('<small><a href="$urlFilterByFoCaId' + element + '" title="Выполнить отбор по этому контрагенту за сегодня">' + result + '</a></small>');
            }
        });
    });
} // faceCounteragents()

// Функция выполняет установку периода в зависимости от переданного значения.
//
function setPredefinedDate(start, end) {
    if (end == null) end = start;
    $("#pbxcallssearch-searchcallperiodstart").val(start.format("YYYY-MM-DD"));
    $("#pbxcallssearch-searchcallperiodend").val(end.format("YYYY-MM-DD"));

    $("#pbxcallssearch-searchcallperiodstart-disp").val(start.format("DD.MM.YYYY"));
    $("#pbxcallssearch-searchcallperiodend-disp").val(end.format("DD.MM.YYYY"));
} // setPredefinedDate()

// Обработчик щелчка по кнопкам с предустановленными периодами.
//
function btnSetPredefinedPeriod() {
    period = $(this).attr("data-id");
    switch (period) {
        case "$ppToday":
            setPredefinedDate(moment().startOf('day'));
            break;
        case "$ppYesterday":
            setPredefinedDate(moment().add(-1, 'days'));
            break;
        case "$ppWeek":
            setPredefinedDate(moment().weekday(0), moment().weekday(6));
            break;
        case "$ppMonth":
            setPredefinedDate(moment().startOf('month'), moment().endOf('month'));
            break;
    }

    return false;
} // btnSetPredefinedPeriod()

// Обработчик щелчка по кнопке "Новый контрагент" в любой строке списке. Выполняет переключение этого признака.
//
function btnToggleNewOnClick() {
    \$btn = $(this); // чтобы внутри функции ответа после post-запроса было видно эту кнопку
    id = \$btn.attr("data-id");
    if (id != "" && id != undefined) {
        \$btn.html("<i class=\"fa fa-cog fa-spin\"></i>");
        $.post("$urlToggleNew", {id:id}, function(data) {
            if (data == true) {
                \$btn.html('<i class="fa fa-asterisk" aria-hidden="true"></i>');
                \$btn.removeClass("btn-default").addClass("btn-success");
            }
            else {
                \$btn.html("Новый");
                \$btn.removeClass("btn-success").addClass("btn-default");                
            }
        });
    }

    return false;
} // btnToggleNewOnClick()

// Обработчик щелчка по кнопкам "Комментарии" в любой строке списке.
//
function btnShowCommentsOnClick() {
    id = $(this).attr("data-id");
    if (id != "" && id != undefined) {
        $("#modal_title").text("$lblWindowHeaderComments");
        $("#modal_body").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#modalWindow").modal();
        $("#modal_body").load("$urlShowComments?call_id=" + id);
    }

    return false;
} // btnShowCommentsOnClick()

// Обработчик щелчка по кнопкам "Идентифицировать контрагента" в любой строке списка.
//
function btnIdentifyCounteragentOnClick() {
    id = $(this).attr("data-id");
    if (id != "" && id != undefined) {
        $("#modal_title").text("$lblWindowHeaderIdentifyCounteragent");
        $("#modal_body").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#modalWindow").modal();
        $("#modal_body").load("$urlIdentifyFoCa?id=" + id);
    }

    return false;
} // btnIdentifyCounteragentOnClick()

// Обработчик щелчка по кнопке "Добавить номер к выбранному контрагенту" при идентификации вручную.
//
function btnApplyIdentificationOnClick() {
    $("#btnApplyIdentification").html('<i class="fa fa-spinner fa-pulse fa-fw text-primary"></i><span class="sr-only">Подождите...</span>');
    $.post("$urlApplyCounteragentsIdentification", $("#frmIdentifyCounteragent").serialize(), function(retval) {
        if (retval.result == true) {
            $("#btnApplyIdentification").html('<i class="fa fa-check-circle" aria-hidden="true"></i> Звонки успешно персоницированы.');
            var element;
            if (retval.replaceAll == true) {
                // заменяем наименование у всех кнопок с таким номером телефона
                element = $("a[data-phone='" + retval.phone + "']");                
            }
            else {
                // заменяем только у текущей кнопки наименование
                element = $("#btnIdentifyCounteragent" + retval.call_id);
            }
            element.replaceWith('<small><a href="$urlFilterByFoCaId' + retval.ca_id + '" title="Выполнить отбор по этому контрагенту за сегодня">' + retval.ca_name + '</a></small>');

            $("#modalWindow").modal("hide");
        }
        else {
            $("#btnApplyIdentification").html("$lblButtonSumbitIdentification");
        }
    });
} // btnApplyIdentificationOnClick()

// Обработчик щелчка по кнопке "Воспроизведение" в отдельной строке таблицы.
//
function btnPlayOnClick() {
    $("#mvPhoneConversation").modal();

    my_jPlayer = $("#jquery_jplayer_calls");
    my_jPlayer.jPlayer("setMedia", {
        mp3: $(this).attr("href")
    });
    my_jPlayer.jPlayer("play");

    return false;
} // btnPlayOnClick()

// Обработчик щелчка по ссылке "Отметить все".
//
function checkAllOnClick() {
    if (checkedFiles) {
    operation = "uncheck";
    checkedFiles = false;
    }
    else {
        operation = "check";
        checkedFiles = true;
    }

    $("input[name ^= 'selection[]']").iCheck(operation);
    recountSelectedFiles();

    return false;
} // checkAllOnClick()

// Выполняет пересчет количества выделенных пользователем файлов и подставляет отличное от нуля значение в текст кнопки.
//
function recountSelectedFiles() {
    var count = $("input[name ^= 'selection[]']:checked").length;
    var prompt = "$btnTransribeFewPrompt";
    if (count > 0) {
        prompt += " <strong>(" + count + ")</strong>";
    }

    $("#$btnTransribeFewId").html(prompt);
} // recountSelectedFiles()

// Обработчик щелчка по ссылке "Распознать выделенные файлы".
//
function transcribeSelectedFilesOnClick() {
    var ids = $("#$gwConversationsId").yiiGridView("getSelectedRows");
    if (ids == "") return false;

    if (confirm("Распознание производится в несколько этапов. Для начала файлы будут отправлены на сервер Яндекса и поставлены в очередь, поскольку процесс распознания занимает время. Когда запись будет распознана (наш робот автоматически отслеживает распознанные записи), на этой странице в каждой такой строке появится соответствующий значок. Продолжить?")) {
        \$body = $("body");
        \$body.addClass("loading");
        $.post("$urlTranscribeSelected", {ids:ids}, function(response) {
            \$body.removeClass("loading");

            // снимаем все отметки
            $("input[name ^= 'selection[]']").iCheck("uncheck");

            if (response == true) {
                alert("Все звонки успешно поставлены в очередь!");
            }
            else {
                $.each(response.ids, function (index, element) {
                    $("input[value = '" + element + "']").iCheck("check");
                });

                message = "";
                $.each(response.errors, function (index, element) {
                    message += "\\r\\n" + element;
                });

                alert("Не все звонки были отправлены на распознавание. Проблемные остаются отмеченными." + message);
            }
        }).always(function() {
            \$body.removeClass("loading");
        });
    }

    return false;
} // transcribeSelectedFilesOnClick()

// подпишем идентифицированных контрагентов их именами
faceCounteragents();

$(document).on("click", "a[id ^= 'btnPlay']", btnPlayOnClick);
$("#mvPhoneConversation").on('hidden.bs.modal', function () {
    my_jPlayer = $("#jquery_jplayer_calls");
    my_jPlayer.jPlayer("stop");
});

// кнопки установки предопределенного периода: Сегодня, Вчера, Неделя, Месяц
$(document).on("click", "button[id ^= 'btnPredefined']", btnSetPredefinedPeriod);

// щелчок по одной из кнопок "Отметить звонок как новый" в списке записей
$(document).on("click", "a[id ^= 'btnToggleNew']", btnToggleNewOnClick);

// щелчок по одной из кнопок "Комментарии" в списке записей
$(document).on("click", "a[id ^= 'btnShowComments']", btnShowCommentsOnClick);

// щелчок по одной из кнопок "Идентифицировать контрагента" в списке записей
$(document).on("click", "a[id ^= 'btnIdentifyCounteragent']", btnIdentifyCounteragentOnClick);

$(document).on("click", "#btnApplyIdentification", btnApplyIdentificationOnClick);

// выделение и отправка на распознание звонков
$(document).on("change ifChanged", "input[name ^= 'selection[]']", recountSelectedFiles);
$(document).on("click ifClicked", ".select-on-check-all", checkAllOnClick);
$(document).on("click", "#$btnTransribeFewId", transcribeSelectedFilesOnClick);
JS
, View::POS_READY);
?>
