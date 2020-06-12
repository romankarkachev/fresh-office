<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use common\models\Ferrymen;
use common\models\UploadingFilesMeanings;

/* @var $this yii\web\View */
/* @var $model common\models\Drivers */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $files array массив приаттаченных к текущий модели файлов */

if (isset($files)) {
    $key = array_search(UploadingFilesMeanings::ТИП_КОНТЕНТА_ПАСПОРТ_ГЛАВНАЯ, array_column($files, 'ufm_id'));
    if (false !== $key) {
        $filePassportFace = $files[$key];
        unset($key);
    }

    $key = array_search(UploadingFilesMeanings::ТИП_КОНТЕНТА_ПАСПОРТ_ПРОПИСКА, array_column($files, 'ufm_id'));
    if (false !== $key) {
        $filePassportReverse = $files[$key];
        unset($key);
    }

    $key = array_search(UploadingFilesMeanings::ТИП_КОНТЕНТА_ВУ_ЛИЦЕВАЯ, array_column($files, 'ufm_id'));
    if (false !== $key) {
        $fileDlFace = $files[$key];
        unset($key);
    }

    $key = array_search(UploadingFilesMeanings::ТИП_КОНТЕНТА_ВУ_ОБОРОТ, array_column($files, 'ufm_id'));
    if (false !== $key) {
        $fileDlReverse = $files[$key];
        unset($key);
    }
}
?>

<div class="drivers-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <?php if ($model->ferryman != null): ?>
        <?= $form->field($model, 'ferryman_id')->hiddenInput()->label(false) ?>

        <?php else: ?>
        <div class="col-md-2 col-lg-2">
            <?= $form->field($model, 'ferryman_id')->widget(Select2::className(), [
                'data' => Ferrymen::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <?php endif ?>
        <div class="col-md-3 col-lg-2">
            <?= $form->field($model, 'surname')->textInput(['maxlength' => true, 'autofocus' => true, 'placeholder' => 'Фамилия']) ?>

        </div>
        <div class="col-md-3 col-lg-2">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Имя']) ?>

        </div>
        <div class="col-md-3 col-lg-2">
            <?= $form->field($model, 'patronymic')->textInput(['maxlength' => true, 'placeholder' => 'Не обязательно']) ?>

        </div>
        <div class="col-md-3 col-lg-2">
            <?= $form->field($model, 'state_id')->widget(Select2::className(), [
                'data' => Ferrymen::arrayMapOfStatesForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'driver_license')->textInput(['maxlength' => true, 'placeholder' => 'Номер водит. удост.']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'dl_issued_at')->widget(DateControl::className(), [
                'value' => $model->dl_issued_at,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => [
                        'placeholder' => 'Выберите дату выдачи',
                    ],
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'phone', ['template' => '{label}<div class="input-group"><span class="input-group-addon">+7</span>{input}</div>{error}'])->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '(999) 999-99-99',
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите номер телефона']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'phone2', ['template' => '{label}<div class="input-group"><span class="input-group-addon">+7</span>{input}</div>{error}'])->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '(999) 999-99-99',
            ])->textInput(['maxlength' => true, 'placeholder' => 'Введите номер телефона']) ?>

        </div>
        <div class="col-md-1" title="Есть допуск на перевозку опасных грузов">
            <label for="<?= strtolower($model->formName()) ?>-is_dopog" class="control-label">ДОПОГ</label>
            <?= $form->field($model, 'is_dopog')->checkbox()->label(false) ?>

        </div>
        <div class="col-md-2">
            <label for="<?= strtolower($model->formName()) ?>-has_smartphone" class="control-label"><?= $model->getAttributeLabel('has_smartphone') ?></label>
            <?= $form->field($model, 'has_smartphone')->checkbox()->label(false) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'pass_serie')->textInput(['placeholder' => 'Паспорт серия']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'pass_num')->textInput(['placeholder' => 'Паспорт номер']) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'pass_issued_at')->widget(DateControl::className(), [
                'value' => $model->pass_issued_at,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => [
                        'placeholder' => 'Паспорт выдан',
                    ],
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ]) ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'pass_issued_by')->textInput(['placeholder' => 'Введите орган, кем выдан паспорт']) ?>

        </div>
    </div>
    <?php if (!$model->isNewRecord): ?>
    <div class="row">
        <div class="col-lg-3">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <i class="fa fa-camera-retro bg-info p-3 font-2xl mr-3 float-left"></i>
                    <?php if (isset($filePassportFace)): ?>
                    <?= Html::a(
                        Ferrymen::getAfd(
                            Ferrymen::fetchAttachedToDriversFilesDescriptions(),
                            UploadingFilesMeanings::ТИП_КОНТЕНТА_ПАСПОРТ_ГЛАВНАЯ,
                            'title'
                        ),
                        '#',
                        UploadingFilesMeanings::optionsForAttachedFilesLink($filePassportFace))
                    ?>
                    <?php else: ?>
                    <?= Ferrymen::getAfd(
                        Ferrymen::fetchAttachedToDriversFilesDescriptions(),
                        UploadingFilesMeanings::ТИП_КОНТЕНТА_ПАСПОРТ_ГЛАВНАЯ,
                        'title'
                    ) ?>
                    <?php endif; ?>

                </div>
                <div class="panel-body">
                    <div class="text-muted text-uppercase font-weight-bold font-xs">
                        <?= Ferrymen::getAfd(
                            Ferrymen::fetchAttachedToDriversFilesDescriptions(),
                            UploadingFilesMeanings::ТИП_КОНТЕНТА_ПАСПОРТ_ГЛАВНАЯ,
                            'hint'
                        ) ?>

                    </div>
                    <?= $form->field($model, 'filePassportFace')->fileInput()->label(false) ?>

                </div>
                <div class="panel-footer">
                    <?php if (isset($filePassportFace)): ?>
                    <?= Html::a('Скачать <i class="fa fa-cloud-download float-right font-lg"></i>', ['/ferrymen-drivers/download-file', 'id' => $filePassportFace['id']], [
                        'class' => 'font-weight-bold font-xs btn-block text-muted',
                    ]) ?>

                    <?php else: ?>
                    <small class="font-xs btn-block text-muted">нет файла</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <i class="fa fa-camera-retro bg-info p-3 font-2xl mr-3 float-left"></i>
                    <?php if (isset($filePassportReverse)): ?>
                    <?= Html::a(
                        Ferrymen::getAfd(
                            Ferrymen::fetchAttachedToDriversFilesDescriptions(),
                            UploadingFilesMeanings::ТИП_КОНТЕНТА_ПАСПОРТ_ПРОПИСКА,
                            'title'
                        ),
                        '#',
                        UploadingFilesMeanings::optionsForAttachedFilesLink($filePassportReverse))
                    ?>
                    <?php else: ?>
                    <?= Ferrymen::getAfd(
                        Ferrymen::fetchAttachedToDriversFilesDescriptions(),
                        UploadingFilesMeanings::ТИП_КОНТЕНТА_ПАСПОРТ_ПРОПИСКА,
                        'title'
                    ) ?>
                    <?php endif; ?>

                </div>
                <div class="panel-body">
                    <div class="text-muted text-uppercase font-weight-bold font-xs"><?= Ferrymen::getAfd(
                        Ferrymen::fetchAttachedToDriversFilesDescriptions(),
                        UploadingFilesMeanings::ТИП_КОНТЕНТА_ПАСПОРТ_ПРОПИСКА,
                        'hint'
                    ) ?></div>
                    <?= $form->field($model, 'filePassportReverse')->fileInput()->label(false) ?>

                </div>
                <div class="panel-footer">
                    <?php if (isset($filePassportReverse)): ?>
                    <?= Html::a('Скачать <i class="fa fa-cloud-download float-right font-lg"></i>', ['/ferrymen-drivers/download-file', 'id' => $filePassportReverse['id']], [
                        'class' => 'font-weight-bold font-xs btn-block text-muted',
                    ]) ?>

                    <?php else: ?>
                    <small class="font-xs btn-block text-muted">нет файла</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <i class="fa fa-camera-retro bg-info p-3 font-2xl mr-3 float-left"></i>
                    <?php if (isset($fileDlFace)): ?>
                    <?= Html::a(
                        Ferrymen::getAfd(
                            Ferrymen::fetchAttachedToDriversFilesDescriptions(),
                            UploadingFilesMeanings::ТИП_КОНТЕНТА_ВУ_ЛИЦЕВАЯ,
                            'title'
                        ),
                        '#',
                        UploadingFilesMeanings::optionsForAttachedFilesLink($fileDlFace))
                    ?>
                    <?php else: ?>
                    <?= Ferrymen::getAfd(
                        Ferrymen::fetchAttachedToDriversFilesDescriptions(),
                        UploadingFilesMeanings::ТИП_КОНТЕНТА_ВУ_ЛИЦЕВАЯ,
                        'title'
                    ) ?>
                    <?php endif; ?>

                </div>
                <div class="panel-body">
                    <div class="text-muted text-uppercase font-weight-bold font-xs"><?= Ferrymen::getAfd(
                        Ferrymen::fetchAttachedToDriversFilesDescriptions(),
                        UploadingFilesMeanings::ТИП_КОНТЕНТА_ВУ_ЛИЦЕВАЯ,
                        'hint'
                    ) ?></div>
                    <?= $form->field($model, 'fileDlFace')->fileInput()->label(false) ?>

                </div>
                <div class="panel-footer">
                    <?php if (isset($fileDlFace)): ?>
                    <?= Html::a('Скачать <i class="fa fa-cloud-download float-right font-lg"></i>', ['/ferrymen-drivers/download-file', 'id' => $fileDlFace['id']], [
                        'class' => 'font-weight-bold font-xs btn-block text-muted',
                    ]) ?>

                    <?php else: ?>
                    <small class="font-xs btn-block text-muted">нет файла</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <i class="fa fa-camera-retro bg-info p-3 font-2xl mr-3 float-left"></i>
                    <?php if (isset($fileDlReverse)): ?>
                    <?= Html::a(
                        Ferrymen::getAfd(
                            Ferrymen::fetchAttachedToDriversFilesDescriptions(),
                            UploadingFilesMeanings::ТИП_КОНТЕНТА_ВУ_ОБОРОТ,
                            'title'
                        ),
                        '#',
                        UploadingFilesMeanings::optionsForAttachedFilesLink($fileDlReverse))
                    ?>
                    <?php else: ?>
                    <?= Ferrymen::getAfd(
                        Ferrymen::fetchAttachedToDriversFilesDescriptions(),
                        UploadingFilesMeanings::ТИП_КОНТЕНТА_ВУ_ОБОРОТ,
                        'title'
                    ) ?>
                    <?php endif; ?>

                </div>
                <div class="panel-body">
                    <div class="text-muted text-uppercase font-weight-bold font-xs"><?= Ferrymen::getAfd(
                        Ferrymen::fetchAttachedToDriversFilesDescriptions(),
                        UploadingFilesMeanings::ТИП_КОНТЕНТА_ВУ_ОБОРОТ,
                        'hint'
                    ) ?></div>
                    <?= $form->field($model, 'fileDlReverse')->fileInput()->label(false) ?>

                </div>
                <div class="panel-footer">
                    <?php if (isset($fileDlReverse)): ?>
                    <?= Html::a('Скачать <i class="fa fa-cloud-download float-right font-lg"></i>', ['/ferrymen-drivers/download-file', 'id' => $fileDlReverse['id']], [
                        'class' => 'font-weight-bold font-xs btn-block text-muted',
                    ]) ?>

                    <?php else: ?>
                    <small class="font-xs btn-block text-muted">нет файла</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="form-group">
        <?php if ($model->ferryman != null): ?>
        <div class="btn-group">
            <button class="btn btn-default btn-lg dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-arrow-left" aria-hidden="true"></i> Вернуться <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><?= Html::a($model->ferryman->name, ['/ferrymen/update', 'id' => $model->ferryman->id], ['title' => 'Вернуться в карточку перевозчика. Изменения не будут сохранены']) ?></li>
                <li><?= Html::a('Водители перевозчика', ['/ferrymen-drivers', 'DriversSearch' => ['ferryman_id' => $model->ferryman->id]], ['title' => 'Перейти в список водителей перевозчика. Изменения не будут сохранены']) ?></li>
            </ul>
        </div>
        <?php else: ?>
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Водители', ['/ferrymen-drivers'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список водителей. Изменения не будут сохранены']) ?>

        <?php endif; ?>
        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::a('Перевести к другому перевозчику', '#', [
            'class' => 'btn btn-default btn-lg',
            'id' => 'btnReplaceFerrymanForm',
        ]) ?>

        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

        <?php if (empty($model->user_id)): ?>
        <?= Html::a('<i class="fa fa-user-plus"></i> Создать учетную запись', ['create-user', 'driver_id' => $model->id], [
            'class' => 'btn btn-success btn-lg',
            'data' => [
                'confirm' => 'Водителю будет предоставлен доступ в систему через мобильное приложение, учетная запись ' . \common\models\Drivers::FOREIGN_DRIVER_LOGIN_PREFIX . $model->id . '. Продолжить?',
                'method' => 'post',
            ],
            'title' => 'Создать пользователя этому водителю для доступа в систему',
        ]) ?>

        <?php endif; ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<div id="mwPreview" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="modal_title" class="modal-title">Предпросмотр файла</h4>
            </div>
            <div id="modal_body_preview" class="modal-body">
                <p>One fine body…</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<?php
$urlPreview = Url::to(['/ferrymen-drivers/preview-file']);
$urlFerrymanReplacingForm = Url::to(['/ferrymen-drivers/replace-ferryman-form']);
$urlReplaceFerryman = Url::to(['/ferrymen-drivers/replace-ferryman']);

$this->registerJs(<<<JS
$("input").iCheck({checkboxClass: "icheckbox_square-green"});

// Обработчик щелчка по ссылкам в колонке "Наименование" в таблице файлов.
//
function previewFileOnClick() {
    id = $(this).attr("data-id");
    if (id != "") {
        $("#modal_title").text("Предпросмотр файла");
        $("#modal_body_preview").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#mwPreview").modal();
        $("#modal_body_preview").load("$urlPreview?id=" + id);
    }

    return false;
} // previewFileOnClick()

// Обработчик щелчка по кнопке "Перевести к другому перевозчику".
// Отображает форму отправки приглашения.
//
function btnReplaceFerrymanFormOnClick() {
    $("#modal_title").text("Перевод к другому перевозчику");
    $("#modal_body_preview").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
    $("#mwPreview").modal();
    $("#modal_body_preview").load("$urlFerrymanReplacingForm?id=$model->id");

    return false;
} // btnReplaceFerrymanFormOnClick()

// Обработчик щелчка по кнопке "Выполнить перевод".
//
function btnReplaceFerrymanOnClick() {
    $.post("$urlReplaceFerryman", $("#frmReplaceFerryman").serialize(), function(result) {
        if (result == true) {
            alert("Водитель был успешно переведен к другому перевозчику.");
            $("#mwPreview").modal("hide");
        }
    });

    return false;
} // btnReplaceFerrymanOnClick()

$(document).on("click", "a[id ^= 'previewFile']", previewFileOnClick);
$(document).on("click", "#btnReplaceFerrymanForm", btnReplaceFerrymanFormOnClick);
$(document).on("click", "#btnReplaceFerryman", btnReplaceFerrymanOnClick);
JS
, yii\web\View::POS_READY);
?>
