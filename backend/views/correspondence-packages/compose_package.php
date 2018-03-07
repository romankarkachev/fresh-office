<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\PostDeliveryKinds;

/* @var $this yii\web\View */
/* @var $model common\models\ComposePackageForm */
/* @var $form yii\bootstrap\ActiveForm */

$inputGroupTemplate = "{label}\n<div class=\"input-group\">\n{input}\n<span class=\"input-group-btn\"><button class=\"btn btn-default\" type=\"button\" id=\"btnTrackNumber\"><i class=\"fa fa-search\" aria-hidden=\"true\"></i> Отследить</button></span></div>\n{error}";
$this->title = 'Формирование почтового отправления | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Пакеты корреспонденции', 'url' => ['/correspondence-packages']];
$this->params['breadcrumbs'][] = 'Формирование почтового отправления';
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

    <?= $form->field($model, 'isReplacePad')->checkbox()->label(null, ['style' => 'padding-left: 0px;']) ?>

    <div class="row">
        <div class="col-md-3">
            <?php
            if (is_array($model->tpPad)) echo $this->render('_pad', [
                'model' => $model,
                'form' => $form,
                'pad' => $model->tpPad,
            ]); ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
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
        case "' . PostDeliveryKinds::DELIVERY_KIND_MAJOR_EXPRESS . '":
            $("#block-track_num").hide();
            break;
        case "' . PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ . '":
            $("#block-track_num").show();
            break;
    }
}',
                ],
            ]) ?>

        </div>
        <div id="block-track_num"<?= $model->pd_id == PostDeliveryKinds::DELIVERY_KIND_ПОЧТА_РФ ? '' : ' class="collapse"' ?>>
            <div class="col-md-1">
                <?= $form->field($model, 'zip_code')->textInput(['readonly' => true]) ?>

            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'address')->textInput(['readonly' => true]) ?>

            </div>
            <div class="col-md-2">
                <?php
                if (count($model->contactPersons) > 1)
                    echo $form->field($model, 'contactPersons')->widget(Select2::className(), [
                            'data' => $model->contactPersons,
                            'theme' => Select2::THEME_BOOTSTRAP,
                            'options' => ['placeholder' => '- выберите -'],
                            'hideSearch' => true,
                            'pluginEvents' => [
                                'change' => 'function() {
        var data = $(this).select2("data");
        $("#composepackageform-contact_person").val(data[0].text);
    }',
                            ],
                        ]) . $form->field($model, 'contact_person')->hiddenInput()->label(false);
                else
                    echo $form->field($model, 'contact_person')->textInput(['readonly' => true]);
                ?>

            </div>
        </div>
    </div>
    <div class="form-group">
        <p>Этот инструмент позволяет выполнить одинаковые действия сразу для нескольких пакетов корреспонденции.
            Идентификаторы пакетов, к которым будут применены изменения, находятся в первом поле. Вы можете назначить
            всем выбранным пакетам одинаковую табличную часть с видами документов, которые включаются в почтовое
            отправление (только, если установлена галочка &laquo;Заменить в выбранных пакетах табличную часть&raquo;).
            Вы можете установить всем выбранным пакетам единый способ доставки. В случае, если выбран способ
            &laquo;Почта России&raquo;, то будет сгенерирован трек-номер, который также появится в личном кабинете на
            сайты Почты России. Для генерации трек-номера в этом случае необходимо, чтобы адреса у всех выбранных пакетов
            совпадали, а также было выбрано одно контактное лицо (если они разные). Если адрес один и контактное лицо
            для всех пакетов одно, то они будут выбраны автоматически, и делать здесь ничего не придется.
        </p>
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Пакеты корреспонденции', ['/correspondence-packages'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?= Html::submitButton('<i class="fa fa-cog"></i> Выполнить', ['class' => 'btn btn-success btn-lg']) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$name = $model->formName() . '[tpPad]';

$this->registerJs(<<<JS
var checked = false;
$("input").iCheck({
    checkboxClass: "icheckbox_square-green"
});

// Обработчик щелчка по ссылке "Отметить все документы".
//
function checkAllDocumentsOnClick() {
    if (checked) {
        operation = "uncheck";
        checked = false;
    }
    else {
        operation = "check";
        checked = true;
    }

    $("input[name ^= '$name']").iCheck(operation);

    return false;
} // checkAllDocumentsOnClick()

// Обработчик щелчка по ссылке "Отметить наиболее распространенные документы".
//
function checkRegularDocumentsOnClick() {
    var values = ["1", "2", "3" , "4"];
    $("input[name ^= '$name']").iCheck("uncheck");
    $.each(values, function(index, value) {
        $("input[data-id = '" + value + "'").iCheck("check");
    });

    return false;
} // checkRegularDocumentsOnClick()

$(document).on("click", "#checkAllDocuments", checkAllDocumentsOnClick);
$(document).on("click", "#checkRegularDocuments", checkRegularDocumentsOnClick);
JS
, \yii\web\View::POS_READY);
?>
