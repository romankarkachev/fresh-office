<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use \common\models\TendersLogs;
use \common\models\TendersLogsSearch;

/* @var $this yii\web\View */
/* @var $model common\models\TendersLogsSearch */
/* @var $form yii\bootstrap\ActiveForm */

$searchFormId = TendersLogs::DOM_IDS['PJAX_SEARCH_FORM_ID'];
$searchGridViewId = TendersLogs::DOM_IDS['GRIDVIEW_ID'];
$searchTypes = TendersLogsSearch::fetchFilterProgresses();
?>

<div class="tenders-logs-search">
    <?php $form = ActiveForm::begin([
        'id' => $searchFormId,
        'action' => \backend\controllers\TendersController::URL_RENDER_LOGS_LIST_AS_ARRAY,
        'method' => 'get',
        'options' => ['data-pjax' => true],
    ]); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'type', [
                'inline' => true,
            ])->radioList(ArrayHelper::map($searchTypes, 'id', 'name'), [
                'class' => 'btn-group',
                'data-toggle' => 'buttons',
                'unselect' => null,
                'item' => function ($index, $label, $name, $checked, $value) use ($searchTypes) {
                    $hint = '';
                    $key = array_search($value, array_column($searchTypes, 'id'));
                    if ($key !== false && isset($searchTypes[$key]['hint'])) $hint = ' title="' . $searchTypes[$key]['hint'] . '"';

                    return '<label class="btn btn-default' . ($checked ? ' active' : '') . '"' . $hint . '>' .
                        Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn']) . $label . '</label>';
                },
                // radiolist onchange
                'onchange' => new \yii\web\JsExpression(<<<JS
$("#$searchGridViewId").html('<p class="text-muted text-center"><i class="fa fa-spinner fa-pulse fa-fw text-muted"></i> <em>Пожалуйста, подождите...</em></p>');
$("#$searchFormId").submit();
JS
),
            ])->label(false) ?>

        </div>
    </div>
    <?= $form->field($model, 'tender_id')->hiddenInput()->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
