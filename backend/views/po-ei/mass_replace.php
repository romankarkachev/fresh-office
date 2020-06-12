<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\PoEi;
use backend\controllers\PoEiController;

/* @var $this yii\web\View */
/* @var $model common\models\PoEiReplaceForm */

$this->title = 'Замена статей в платежных ордерах | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = PoEiController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Замена статей';

$urlCountRows = Url::to(PoEiController::URL_COUNT_POS_AS_ARRAY);
?>
<div class="po-ei-mass-replace">
    <?php $form = ActiveForm::begin(); ?>

    <div class="form-group">
        <p>В платежных ордерах, где выбрана искомая статья, будет выполнена заменена на новую.</p>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'src_ei_id')->widget(Select2::class, [
                'data' => PoEi::arrayMapByGroupsForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => [
                    'placeholder' => '- выберите -',
                    'title' => 'Старая статья расходов',
                ],
                'pluginEvents' => [
                    'change' => 'function() {
    $.get("' . $urlCountRows . '?id=" + $(this).val(), function(result) {
        if (result != false) $("#block-count-rows").text(result);
    });
}',
                ],
            ]) ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'dest_ei_id')->widget(Select2::class, [
                'data' => PoEi::arrayMapByGroupsForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => [
                    'placeholder' => '- выберите -',
                    'title' => 'Новая статья расходов',
                ],
            ]) ?>

        </div>
        <div class="col-md-2">
            <label for="<?= strtolower($model->formName()) ?>-drop_released" class="control-label"><?= $model->getAttributeLabel('drop_released') ?></label>
            <?= $form->field($model, 'drop_released')->checkbox()->label(false) ?>

        </div>
    </div>
    <div id="block-count-rows" class="form-group"></div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . PoEiController::ROOT_LABEL, Url::to(PoEiController::ROOT_URL_AS_ARRAY), ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?= Html::submitButton('<i class="fa fa-exclamation-circle" aria-hidden="true"></i> Выполнить замену', ['class' => 'btn btn-warning btn-lg']) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
$("input").iCheck({checkboxClass: "icheckbox_square-green"});
JS
, yii\web\View::POS_READY);
?>
