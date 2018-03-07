<?php

use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\PostDeliveryKinds;

/* @var $this yii\web\View */
/* @var $model common\models\ComposePackageForm */
/* @var $form yii\bootstrap\ActiveForm */

$inputGroupTemplate = "{label}\n<div class=\"input-group\">\n{input}\n<span class=\"input-group-btn\"><button class=\"btn btn-default\" type=\"button\" id=\"btnTrackNumber\"><i class=\"fa fa-search\" aria-hidden=\"true\"></i> Отследить</button></span></div>\n{error}";
?>

<div class="compose-package-form">
    <?php $form = ActiveForm::begin([
        'id' => 'frmComposePackage',
        'action' => '/correspondence-packages/compose-package',
    ]); ?>

    <?= $form->field($model, 'packages_ids')->widget(Select2::className(), [
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'tags' => true,
            'tokenSeparators' => [',', ' '],
            'maximumInputLength' => 10
        ],
        'readonly' => true,
    ]) ?>

    <?= $this->render('_pad', [
        'model' => $model,
        'pad' => $model->tpPad,
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'pd_id')->widget(Select2::className(), [
                'data' => PostDeliveryKinds::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
                'pluginEvents' => [
                    'change' => 'function() {
    switch ($(this).val()) {
        case "' . PostDeliveryKinds::DELIVERY_KIND_САМОВЫВОЗ . '":
        case "' . PostDeliveryKinds::DELIVERY_KIND_КУРЬЕР . '":
            $("#block-track_num").hide();
            break;
        case "' . PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ . '":
        case "' . PostDeliveryKinds::DELIVERY_KIND_MAJOR_EXPRESS . '":
            $("#block-track_num").show();
            break;
    }
}',
                ],
            ]) ?>

        </div>
        <div id="block-track_num" class="collapse">
            <div class="col-md-6">
                <?= $form->field($model, 'track_num')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'Введите идентификатор отправления',
                    'title' => 'Введите идентификатор отправления',
                ]) ?>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$name = $model->formName() . '[tpPad]';

$this->registerJs(<<<JS
$("input[name ^= '$name']").iCheck({
    checkboxClass: "icheckbox_square-green"
});
JS
, \yii\web\View::POS_READY);
?>
