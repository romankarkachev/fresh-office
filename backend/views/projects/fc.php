<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use common\models\Regions;

/* @var $this yii\web\View */

$this->title = 'Подбор перевозчика по завершенным проектам | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Подбор перевозчика';

$urlCastFerrymen = Url::to(['/projects/cast-ferryman-by-region']);
?>
<div class="ferrymen-casting">
    <div class="row">
        <div class="col-md-2">
            <div class="form-group">
                <label class="control-label" for="region_id">Выберите регион</label>
                <?= Select2::widget([
                    'id' => 'region_id',
                    'name' => 'FerrymanCastingForm[region_id]',
                    'data' => Regions::arrayMapOnlyRussiaForSelect2(),
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'options' => ['placeholder' => 'выберите регион...'],
                    'pluginEvents' => [
                        'change' => new JsExpression('function() {renderTable();return;}'),
                    ],
                ]) ?>

            </div>
        </div>
        <div class="col-md-2">
            <label for="is_detailed" class="control-label">Детализировать</label>
            <div class="form-group">
                <div class="checkbox" style="margin-top:5px;">
                    <?= Html::input('checkbox', 'FerrymanCastingForm[is_detailed]', 1, ['id' => 'is_detailed']) ?>

                </div>
            </div>
        </div>
    </div>
    <div id="block-table" class="form-group"></div>
</div>
<?php
$this->registerJs(<<<JS

// Обработчик изменения региона или признака необходимости детализированной выборки.
//
function renderTable() {
    region_id = $("#region_id").val();
    if (region_id != "") {
        detailed = $("#is_detailed").prop("checked");
        \$block = $("#block-table");
        \$block.html('<p class="text-center"><i class="fa fa-cog fa-spin fa-2x text-muted"></i><span class="sr-only">Подождите...</span></p>');
        \$block.load("$urlCastFerrymen?region_id=" + region_id + "&is_detailed=" + detailed);
    }
} // renderTable()

JS
, \yii\web\View::POS_BEGIN);

$this->registerJs(<<<JS
$("input").iCheck({checkboxClass: "icheckbox_square-green"});
$("#is_detailed").on("ifChanged", renderTable);
JS
, \yii\web\View::POS_READY);
?>
