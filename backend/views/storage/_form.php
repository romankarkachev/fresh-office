<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use common\models\TransportRequests;
use common\models\UploadingFilesMeanings;

/* @var $this yii\web\View */
/* @var $model common\models\FileStorage */
/* @var $form yii\bootstrap\ActiveForm */

$formName = strtolower($model->formName());
$urlFindFolderByName = Url::to(['/storage/find-folder-by-name']);
?>

<div class="file-storage-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'ca_id')->widget(Select2::className(), [
                'initValueText' => TransportRequests::getCustomerName($model->ca_id),
                'theme' => Select2::THEME_BOOTSTRAP,
                'language' => 'ru',
                'options' => ['placeholder' => 'Введите наименование'],
                'pluginOptions' => [
                    'minimumInputLength' => 3,
                    'language' => 'ru',
                    'ajax' => [
                        'url' => Url::to(['projects/direct-sql-counteragents-list']),
                        'delay' => 500,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(result) { return result.text; }'),
                    'templateSelection' => new JsExpression('function (result) {
    if (!result.id) {return result.text;}
    $("#' . $formName . '-ca_name" ).val(result.text);
    return result.text;
}'),
                ],
                'pluginEvents' => [
                    'change' => new JsExpression('function() {
    var data = $(this).select2("data");
    id = data[0].id;
    name = data[0].text;
    if (id != "" && id != undefined && name != "" && name != undefined) {
        $.get("' . $urlFindFolderByName . '?id=" + id + "&name=" + name, function(result) {
            if (result != false) {
                $("#block-folder").html(result);
            }
        });        
    }
}'),
                ],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'type_id')->widget(Select2::className(), [
                'data' => UploadingFilesMeanings::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <?php if ($model->isNewRecord): ?>
        <div class="col-md-2">
            <?= $form->field($model, 'file')->fileInput() ?>

        </div>
        <?php endif; ?>
        <?= $form->field($model, 'ca_name')->hiddenInput()->label(false) ?>

    </div>
    <div id="block-folder"></div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Файловое хранилище', ['/storage'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$urlFindFolderByName = Url::to(['/storage/find-folder-by-name']);

$this->registerJs(<<<JS
// Обработчик изменения значения в поле "Контрагент".
//
function caOnChange() {
    var data = $(this).select2("data");
    //$("#$formName-name").val(data[0].text);
    /*
    ca = $("input[name='FileStorage[ca_id]']:checked").val();
    alert(ca);return;
    */
    id = data[0].id;
    name = data[0].text;
    if (id != "" && id != undefined && name != "" && name != undefined) {
        $.get("$urlFindFolderByName?id=" + id + "&name=" + name, function(result) {
            if (result != false) {
                $("#block-folder").html(result);
            }
        });        
    }
} // caOnChange()
JS
, \yii\web\View::POS_READY);
?>