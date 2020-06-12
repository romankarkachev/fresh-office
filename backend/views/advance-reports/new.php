<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use backend\controllers\AdvanceReportsController;

/* @var $this yii\web\View */
/* @var $model common\models\AdvanceReportForm */

$this->title = 'Новый авансовый отчет | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = AdvanceReportsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Создание новых *';

$btnAddAdvanceReportId = 'btnAddAdvanceReport';
$emptyPosBlockPrompt = '<p class="card-text text-muted">Нет авансовых отчетов.</p>';

if (is_array($model->crudePos))
    $posCount = count($model->crudePos);
else
    $posCount = 0;

$urlAddPoRow = Url::to(AdvanceReportsController::URL_RENDER_PO_ROW_AS_ARRAY);
$blockPosId = 'block-pos';
$poRowId = 'po-row';
$poPreloaderId = 'po-preloader';
$btnDeletePoId = 'btnDeleteRow';
?>
<div class="po-create">
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="panel panel-success">
        <div class="panel-heading">
            Авансовые отчеты <span id="<?= $poPreloaderId ?>" class="collapse"><i class="fa fa-cog fa-spin text-muted"></i></span>
            <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i> Добавить', '#', ['id' => $btnAddAdvanceReportId, 'class' => 'btn btn-success btn-xs pull-right', 'data-count' => $posCount, 'title' => 'Добавить авансовый отчет']) ?>

        </div>
        <div class="panel-body">
            <div id="<?= $blockPosId ?>">
                <?php
                if ($posCount == 0)
                    echo $emptyPosBlockPrompt;
                else
                    foreach ($model->crudePos as $index => $row)
                        echo $this->render('_row_po_fields', [
                            'model' => $row,
                            'formModel' => $model,
                            'form' => $form,
                            'counter' => $index,
                            'count' => $posCount,
                        ]);
                ?>

            </div>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
// Обработчик щелчка по кнопке "Добавить банковский счет" в блоке "Банковские счета".
//
function btnAddNewPoOnClick() {
    \$btnAdd = $("#$btnAddAdvanceReportId");
    counter = parseInt(\$btnAdd.attr("data-count"));
    next_counter = counter+1;
    $("#$poPreloaderId").show();
    $.get("$urlAddPoRow?counter=" + counter, function(data) {
        \$block = $("#$blockPosId");
        if ($("div[id ^= '$poRowId-']").length == 0) \$block.html("");
        \$block.append(data);
        $("#$poPreloaderId").hide();
    });
    
    // наращиваем количество добавленных строк
    \$btnAdd.attr("data-count", next_counter);
    
    return false;
} // btnAddNewPoOnClick()

// Обработчик щелчка по кнопке "Удалить банковский счет" в блоке "Банковские счета".
//
function btnDeleteNewPoOnClick(event) {
    var counter = $(this).attr("data-counter");
    $("#$poRowId-" + counter).fadeOut(300, function() {
        $(this).remove();
        if ($("div[id ^= '$poRowId-']").length == 0) $("#$blockPosId").html('$emptyPosBlockPrompt');
    });

    return false;
} // btnDeleteNewPoOnClick()

$(document).on("click", "#$btnAddAdvanceReportId", btnAddNewPoOnClick);
$(document).on("click", "a[id ^= '$btnDeletePoId']", btnDeleteNewPoOnClick);
JS
, yii\web\View::POS_READY);
?>
