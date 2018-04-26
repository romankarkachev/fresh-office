<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\rating\StarRating;

/* @var $this yii\web\View */
/* @var $ratingProjects array */

$ratingModalTitle = 'Оценка выполнения заказа';
$iconOk = 'fa fa-check-circle-o fa-lg text-success';
$iconFail = 'fa fa-times fa-lg text-danger';
?>
<div class="card">
    <div class="card-header card-header-info card-header-inverse">Оценка нашего сотрудничества</div>
    <div class="card-block">
        <table class="table table-responsive-sm table-hover table-outline mb-0">
            <thead class="thead-light">
            <tr>
                <th>Заказ</th>
                <th width="250">Оценка</th>
                <th width="20"></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($ratingProjects as $project): ?>
                <tr>
                    <td>
                        <div>№ <?= $project['ID_LIST_PROJECT_COMPANY'] ?> от <?= Yii::$app->formatter->asDate($project['DATE_CREATE_PROGECT'], 'php:d F Y г.') ?></div>
                        <div class="small text-muted">
                            Дата вывоза: <?= !empty($project['ADD_vivozdate']) ? Yii::$app->formatter->asDate($project['ADD_vivozdate'], 'php:d F Y г.') : 'не известно' ?>

                        </div>
                    </td>
                    <td>
                        <?= StarRating::widget([
                            'id' => 'project' . $project['ID_LIST_PROJECT_COMPANY'],
                            'options' => [
                                'data-id' => $project['ID_LIST_PROJECT_COMPANY'],
                                'data-ca_id' => $project['ID_COMPANY'],
                            ],
                            'name' => 'rating_' . $project['ID_LIST_PROJECT_COMPANY'],
                            'pluginOptions' => [
                                'min' => 0,
                                'max' => 5,
                                'step' => 1,
                                'size' => 'sm',
                                'theme' => 'krajee-fa',
                                'showClear' => false,
                                'showCaption' => false,
                            ],
                        ]) ?>

                    </td>
                    <td>
                        <div id="block-result<?= $project['ID_LIST_PROJECT_COMPANY'] ?>"></div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div id="mwRatingForm" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="mtRating" class="modal-title"><?= $ratingModalTitle ?></h4>
                <small id="modal_title_right" class="form-text"></small>
            </div>
            <div id="mbRating" class="modal-body"></div>
            <div class="modal-footer">
                <?= Html::button('<i class="fa fa-paper-plane"></i> Отправить', ['class' => 'btn btn-success', 'id' => 'btnSend']) ?>

                <?= Html::button('Закрыть', ['class' => 'btn btn-secondary', 'data-dismiss' => 'modal']) ?>

            </div>
        </div>
    </div>
</div>
<?php
$urlRatingForm = Url::to(['/default/rating-form']);
$urlRateProject = Url::to(['/default/rate-project']);

$this->registerJs(<<<JS
$("input[id ^= \'project\']").on("rating:change", function(event, value, caption) {
    project_id = $(this).attr("data-id");
    ca_id = $(this).attr("data-ca_id");
    if (project_id != "" && ca_id != "" && project_id != undefined && ca_id != undefined) {
        \$blockResult = $("#block-result" + project_id);

        if (value != 5) {
            \$body = $("#mbRating");
            $("#mtRating").text("$ratingModalTitle " + project_id);
            \$body.html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
            $("#mwRatingForm").modal();
            \$body.load("$urlRatingForm?project_id=" + project_id + "&ca_id=" + ca_id + "&rate=" + value);
        }
        else {
            \$blockResult.html("<i class=\"fa fa-spinner fa-pulse fa-fw text-primary\"></i>");

            $.post("$urlRateProject", {
                project_id: project_id,
                ca_id: ca_id,
                rate: value
            }, function(result) {
                if (result == true)
                    \$blockResult.html("<i class=\"$iconOk\"></i>");
                else
                    \$blockResult.html("<i class=\"$iconFail\"></i>");
            }).fail(function() {
                \$blockResult.html("<i class=\"$iconFail\"></i>");
            }).always(function() {
                $("#mwRatingForm").modal("hide");
            });
        }
    }
});

// Обработчик щелчка по кнопке "Отправить".
// Выполняет отправку оценки с комментарием.
//
function rateProjectOnClick() {
    project_id = $("#projectratingform-project_id").val();
    if (project_id != "") {
        \$blockResult = $("#block-result" + project_id);
        $.post("$urlRateProject", $("#frmRateProject").serialize(), function(result) {
            if (result == true)
                \$blockResult.html("<i class=\"$iconOk\"></i>");
            else
                \$blockResult.html("<i class=\"$iconFail\"></i>");
        }).fail(function() {
            \$blockResult.html("<i class=\"$iconFail\"></i>");
        }).always(function() {
            $("#mwRatingForm").modal("hide");
        });
    }

    return false;
} // rateProjectOnClick()

$(document).on("click", "#btnSend", rateProjectOnClick);
JS
, \yii\web\View::POS_READY);
?>
