<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\MaskedInput;
use common\models\PoEi;
use backend\controllers\PoTemplatesController;

/* @var $this yii\web\View */
/* @var $model common\models\PoAt */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dpProperties array свойства и значения свойств статьи расходов платежного ордера */
?>

<div class="po-at-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'company_id')->widget(Select2::class, [
                'initValueText' => $model->companyName,
                'theme' => Select2::THEME_BOOTSTRAP,
                'language' => 'ru',
                'options' => ['placeholder' => 'Введите наименование (ИНН, ОГРН)'],
                'pluginOptions' => [
                    'minimumInputLength' => 1,
                    'language' => 'ru',
                    'ajax' => [
                        'url' => Url::to(\backend\controllers\CompaniesController::URL_CASTING_AS_ARRAY),
                        'delay' => 500,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(result) { return result.text; }'),
                    'templateSelection' => new JsExpression('function (result) { return result.text; }'),
                ],
            ]) ?>

        </div>
        <div class="col-md-5">
            <?= $form->field($model, 'ei_id')->widget(Select2::class, [
                'data' => PoEi::arrayMapByGroupsForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'pluginEvents' => ['select2:select' => new JsExpression('function() { eiOnChange(); }')],
            ]) ?>

        </div>
        <div class="col-md-3">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'amount', [
                        'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
                    ])->widget(MaskedInput::class, [
                        'clientOptions' => [
                            'alias' =>  'numeric',
                            'groupSeparator' => ' ',
                            'autoUnmask' => true,
                            'autoGroup' => true,
                            'removeMaskOnSubmit' => true,
                        ],
                    ])->textInput([
                        'maxlength' => true,
                        'placeholder' => '0',
                    ]) ?>

                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'periodicity')->widget(MaskedInput::class, [
                        'mask' => '99',
                        'clientOptions' => ['placeholder' => ''],
                    ])->textInput(['maxlength' => true, 'placeholder' => 'Например, 22']) ?>

                </div>
            </div>
        </div>
        <div class="col-md-1">
            <label for="<?= strtolower($model->formName()) ?>-is_active" class="control-label">Активен</label>
            <?= $form->field($model, 'is_active')->checkbox()->label(false) ?>

        </div>
    </div>
    <div id="block-properties" class="form-group"><?php if (!empty($model->ei_id)) echo $this->render('_properties_block', ['model' => $model]) ?></div>
    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите комментарий']) ?>

    <div class="form-group text-muted small">
        <p>Оставьте поле &laquo;Сумма&raquo; пустым, чтобы подставлялось последнее использованное значение.</p>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . PoTemplatesController::MAIN_MENU_LABEL, PoTemplatesController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$formName = $model->formName();
$formNameId = strtolower($model->formName());
$urlRenderProperties = Url::to(\backend\controllers\PoController::URL_RENDER_PROPERTIES_AS_ARRAY);

$this->registerJs(<<<JS

$("input").iCheck({checkboxClass: "icheckbox_square-green"});

// Обработчик изменения значения в поле "Статья расходов".
//
function eiOnChange() {
    ei_id = $("#$formNameId-ei_id").val();
    if (ei_id) {
        url = "$urlRenderProperties?ei_id=" + ei_id;
        \$block = $("#block-properties");
        \$block.html('<p class="text-center"><i class="fa fa-spinner fa-pulse fa-fw text-primary text-muted"></i><span class="sr-only">Подождите...</span></p>');
        \$block.load(url);
    }
} // eiOnChange()

JS
, \yii\web\View::POS_READY);
?>
