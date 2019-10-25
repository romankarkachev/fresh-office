<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use \common\models\TendersFilesSearch;

/* @var $this yii\web\View */
/* @var $model common\models\TendersFilesSearch */
/* @var $form yii\bootstrap\ActiveForm */

$searchFormId = TendersFilesSearch::DOM_IDS['PJAX_SEARCH_FORM_ID'];
$searchGridViewId = TendersFilesSearch::DOM_IDS['GRIDVIEW_ID'];
$searchContentTypes = \common\models\TendersContentTypes::arrayMapForSelect2(true);
?>

<div class="tenders-logs-search">
    <?php $form = ActiveForm::begin([
        'id' => $searchFormId,
        'action' => \backend\controllers\TendersController::URL_RENDER_FILES_LIST_AS_ARRAY,
        'method' => 'get',
        'options' => ['data-pjax' => true],
    ]); ?>

    <?= $form->field($model, 'ct_id', [
        'inline' => true,
    ])->radioList($searchContentTypes, [
        'class' => 'btn-group',
        'data-toggle' => 'buttons',
        'unselect' => null,
        'item' => function ($index, $label, $name, $checked, $value) use ($searchContentTypes) {
            $hint = '';
            $key = array_search($value, array_column($searchContentTypes, 'id'));
            if ($key !== false && isset($searchContentTypes[$key]['hint'])) $hint = ' title="' . $searchContentTypes[$key]['hint'] . '"';

            return '<label class="btn btn-default' . ($checked ? ' active' : '') . '"' . $hint . '>' .
                Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn']) . $label . '</label>';
        },
        // radiolist onchange
        'onchange' => new \yii\web\JsExpression(<<<JS
$("#downloadSelectedFiles").remove();
$("#$searchGridViewId").html('<p class="text-muted text-center"><i class="fa fa-spinner fa-pulse fa-fw text-muted"></i> <em>Пожалуйста, подождите...</em></p>');
$("#$searchFormId").submit();
JS
),
    ])->label(false) ?>

    <?= $form->field($model, 'tender_id')->hiddenInput()->label(false) ?>

    <?php ActiveForm::end(); ?>

</div>
