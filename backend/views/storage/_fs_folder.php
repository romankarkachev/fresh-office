<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\FileStorage */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $ca_id integer id контрагента во Fresh Office */
/* @var $ca_name string наименование контрагента */
/* @var $folderExists bool */
/* @var $folderName string */
/* @var $canChange bool */

$ntcfValue = false; // необходимость создания папки контрагента
$formName = strtolower($model->formName());
?>
<div class="panel panel-warning">
    <div class="panel-body">
        <div class="form-group">
        <?php if (!$canChange): ?>
            <p>У контрагента есть утвержденная папка<?= $ca_name == $folderName ? ' (совпадает с названием контрагента)' : ' (' . $folderName . ')' ?>.</p>
        <?php else: ?>
            <p id="prompt-creating" class="text-danger">
        <?php if ($folderExists): ?>
                Обнаружена папка, которая может быть использована для хранения файлов этого контрагента.
        <?php else: ?>
                Папка контрагента не была обнаружена, и будет создана с id контрагента в наименовании.
            <?php $ntcfValue = true; ?>
        <?php endif; ?>
            </p>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'folder_seek')->widget(Select2::className(), [
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'language' => 'ru',
                    'options' => ['placeholder' => 'Введите наименование папки, если хотите использовать другую'],
                    'pluginOptions' => [
                        'minimumInputLength' => 1,
                        'language' => 'ru',
                        'ajax' => [
                            'url' => Url::to(['storage/casting-by-foldername']),
                            'delay' => 500,
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(result) { return result.text; }'),
                        'templateSelection' => new JsExpression('function (result) {
    if (!result.id) {return result.text;}
    $("#' . $formName . '-needtocreatefolder" ).val(0);
    $("#' . $formName . '-cafoldername" ).val(result.text);
    $("#prompt-creating").text("Будет использована выбранная пользователем папка:");
    return result.text;
}'),
                    ],
                ])->label(false) ?>

            </div>
        </div>
        <!--
        <p>
            <?= Html::a('Оставить', '#', ['id' => 'leaveMeAlone']) ?> или <?= Html::a('Подобрать из списка', '#', ['id' => 'selectFromList']) ?>
            <em class="text-muted">Если Вы ничего не предпримите, то будет принято решение оставить предложенный вариант.</em>
        </p>
        -->
        <p>В любом случае, использованная сейчас папка будет закреплена за этим контрагентом, и в будущем этот вопрос подниматься не будет.</p>
        <?php endif; ?>
        <?= $form->field($model, 'needToCreateFolder')->hiddenInput(['value' => $ntcfValue])->label(false) ?>

        <?= $form->field($model, 'caFolderName')->hiddenInput(['value' => $folderName])->label(false) ?>

    </div>
</div>
