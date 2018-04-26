<?php

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
                    'name' => 'region_id',
                    'data' => Regions::arrayMapOnlyRussiaForSelect2(),
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'options' => ['placeholder' => 'выберите регион...'],
                    'pluginEvents' => [
                        'change' => new JsExpression('function() {
    $block = $("#block-table");
    $block.html("<p class=\"text-center\"><i class=\"fa fa-cog fa-spin fa-2x text-muted\"></i><span class=\"sr-only\">Подождите...</span></p>");
    $block.load("' . $urlCastFerrymen . '?region_id=" + $(this).val());
}'),
                    ],
                ]) ?>

            </div>
        </div>
    </div>
    <div id="block-table" class="form-group"></div>
</div>
<?php
/*
$this->registerJs(<<<JS

JS
, \yii\web\View::POS_READY);
*/
?>
