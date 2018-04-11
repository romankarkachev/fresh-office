<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\Ferrymen;
use common\models\TransportTypes;
use common\models\TransportBrands;
use common\models\UploadingFilesMeanings;

/* @var $this yii\web\View */
/* @var $model common\models\Transport */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $files array массив приаттаченных к текущий модели файлов */

if (isset($files)) {
    $key = array_search(UploadingFilesMeanings::ТИП_КОНТЕНТА_ОСАГО, array_column($files, 'ufm_id'));
    if (false !== $key) {
        $fileOsago = $files[$key];
        unset($key);
    }

    $key = array_search(UploadingFilesMeanings::ТИП_КОНТЕНТА_ПТС_ЛИЦЕВАЯ, array_column($files, 'ufm_id'));
    if (false !== $key) {
        $filePtsFace = $files[$key];
        unset($key);
    }

    $key = array_search(UploadingFilesMeanings::ТИП_КОНТЕНТА_ПТС_ОБОРОТ, array_column($files, 'ufm_id'));
    if (false !== $key) {
        $filePtsReverse = $files[$key];
        unset($key);
    }

    $key = array_search(UploadingFilesMeanings::ТИП_КОНТЕНТА_СТС_ЛИЦЕВАЯ, array_column($files, 'ufm_id'));
    if (false !== $key) {
        $fileStsFace = $files[$key];
        unset($key);
    }

    $key = array_search(UploadingFilesMeanings::ТИП_КОНТЕНТА_СТС_ОБОРОТ, array_column($files, 'ufm_id'));
    if (false !== $key) {
        $fileStsReverse = $files[$key];
        unset($key);
    }

    $key = array_search(UploadingFilesMeanings::ТИП_КОНТЕНТА_ДИАГНОСТИЧЕСКАЯ_КАРТА, array_column($files, 'ufm_id'));
    if (false !== $key) {
        $fileDk = $files[$key];
        unset($key);
    }
}
?>

<div class="transport-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="card">
        <div class="card-block">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'tt_id')->widget(Select2::className(), [
                        'data' => TransportTypes::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'hideSearch' => true,
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'brand_id')->widget(Select2::className(), [
                        'data' => TransportBrands::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                    ]) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'vin')->textInput(['maxlength' => true, 'placeholder' => 'Введите VIN', 'title' => 'VIN-код, номер кузова или номер шасси']) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'rn')->textInput(['maxlength' => true, 'placeholder' => 'Введите госномер']) ?>

                </div>
                <div class="col-md-2">
                    <?= $form->field($model, 'trailer_rn')->textInput(['maxlength' => true, 'placeholder' => 'Госномер прицепа']) ?>

                </div>
            </div>
            <?php if (!$model->isNewRecord): ?>
            <div class="row">
                <div class="col-lg-2">
                    <div class="card">
                        <div class="card-body p-3 clearfix">
                            <i class="fa fa-camera-retro bg-info p-3 font-2xl mr-3 float-left"></i>
                            <?php if (isset($fileOsago)): ?>
                            <div class="h5 text-primary mb-1 mt-2"><?= Html::a(
                                Ferrymen::getAfd(
                                    Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                    UploadingFilesMeanings::ТИП_КОНТЕНТА_ОСАГО,
                                    'title'
                                ),
                                '#',
                                UploadingFilesMeanings::optionsForAttachedFilesLink($fileOsago)) ?>

                            </div>
                            <?php else: ?>
                            <div class="h5 text-primary mb-1 mt-2"><?= Ferrymen::getAfd(
                                Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                UploadingFilesMeanings::ТИП_КОНТЕНТА_ОСАГО,
                                'title'
                            ) ?></div>
                            <?php endif; ?>
                            <div class="text-muted text-uppercase font-weight-bold font-xs"><?= Ferrymen::getAfd(
                                Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                UploadingFilesMeanings::ТИП_КОНТЕНТА_ОСАГО,
                                'hint'
                            ) ?></div>
                            <?= $form->field($model, 'fileOsago')->fileInput()->label(false) ?>

                        </div>
                        <div class="card-footer px-3 py-2">
                            <?php if (isset($fileOsago)): ?>
                            <?= Html::a('Скачать <i class="fa fa-cloud-download float-right font-lg"></i>', ['/transport/download-file', 'id' => $fileOsago['id']], [
                                'class' => 'font-weight-bold font-xs btn-block text-muted',
                            ]) ?>

                            <?php else: ?>
                            <small class="font-xs btn-block text-muted">нет файла</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="card">
                        <div class="card-body p-3 clearfix">
                            <i class="fa fa-camera-retro bg-info p-3 font-2xl mr-3 float-left"></i>
                            <?php if (isset($filePtsFace)): ?>
                            <div class="h5 text-primary mb-1 mt-2"><?= Html::a(
                                Ferrymen::getAfd(
                                    Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                    UploadingFilesMeanings::ТИП_КОНТЕНТА_ПТС_ЛИЦЕВАЯ,
                                    'title'
                                ),
                                '#',
                                UploadingFilesMeanings::optionsForAttachedFilesLink($filePtsFace)) ?>

                            </div>
                            <?php else: ?>
                            <div class="h5 text-primary mb-1 mt-2"><?= Ferrymen::getAfd(
                                Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                UploadingFilesMeanings::ТИП_КОНТЕНТА_ПТС_ЛИЦЕВАЯ,
                                'title'
                            ) ?></div>
                            <?php endif; ?>
                            <div class="text-muted text-uppercase font-weight-bold font-xs"><?= Ferrymen::getAfd(
                                Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                UploadingFilesMeanings::ТИП_КОНТЕНТА_ПТС_ЛИЦЕВАЯ,
                                'hint'
                            ) ?></div>
                            <?= $form->field($model, 'filePtsFace')->fileInput()->label(false) ?>

                        </div>
                        <div class="card-footer px-3 py-2">
                            <?php if (isset($filePtsFace)): ?>
                            <?= Html::a('Скачать <i class="fa fa-cloud-download float-right font-lg"></i>', ['/transport/download-file', 'id' => $filePtsFace['id']], [
                                'class' => 'font-weight-bold font-xs btn-block text-muted',
                            ]) ?>

                            <?php else: ?>
                            <small class="font-xs btn-block text-muted">нет файла</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="card">
                        <div class="card-body p-3 clearfix">
                            <i class="fa fa-camera-retro bg-info p-3 font-2xl mr-3 float-left"></i>
                            <?php if (isset($filePtsReverse)): ?>
                            <div class="h5 text-primary mb-1 mt-2"><?= Html::a(
                                Ferrymen::getAfd(
                                    Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                    UploadingFilesMeanings::ТИП_КОНТЕНТА_ПТС_ОБОРОТ,
                                    'title'
                                ),
                                '#',
                                UploadingFilesMeanings::optionsForAttachedFilesLink($filePtsReverse)) ?>

                            </div>
                            <?php else: ?>
                            <div class="h5 text-primary mb-1 mt-2"><?= Ferrymen::getAfd(
                                Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                UploadingFilesMeanings::ТИП_КОНТЕНТА_ПТС_ОБОРОТ,
                                'title'
                            ) ?></div>
                            <?php endif; ?>
                            <div class="text-muted text-uppercase font-weight-bold font-xs"><?= Ferrymen::getAfd(
                                Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                UploadingFilesMeanings::ТИП_КОНТЕНТА_ПТС_ОБОРОТ,
                                'hint'
                            ) ?></div>
                            <?= $form->field($model, 'filePtsReverse')->fileInput()->label(false) ?>

                        </div>
                        <div class="card-footer px-3 py-2">
                            <?php if (isset($filePtsReverse)): ?>
                            <?= Html::a('Скачать <i class="fa fa-cloud-download float-right font-lg"></i>', ['/transport/download-file', 'id' => $filePtsReverse['id']], [
                                'class' => 'font-weight-bold font-xs btn-block text-muted',
                            ]) ?>

                            <?php else: ?>
                            <small class="font-xs btn-block text-muted">нет файла</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="card">
                        <div class="card-body p-3 clearfix">
                            <i class="fa fa-camera-retro bg-info p-3 font-2xl mr-3 float-left"></i>
                            <?php if (isset($fileStsFace)): ?>
                            <div class="h5 text-primary mb-1 mt-2"><?= Html::a(
                                Ferrymen::getAfd(
                                    Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                    UploadingFilesMeanings::ТИП_КОНТЕНТА_СТС_ЛИЦЕВАЯ,
                                    'title'
                                ),
                                '#',
                                UploadingFilesMeanings::optionsForAttachedFilesLink($fileStsFace)) ?>

                            </div>
                            <?php else: ?>
                            <div class="h5 text-primary mb-1 mt-2"><?= Ferrymen::getAfd(
                                Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                UploadingFilesMeanings::ТИП_КОНТЕНТА_СТС_ЛИЦЕВАЯ,
                                'title'
                            ) ?></div>
                            <?php endif; ?>
                            <div class="text-muted text-uppercase font-weight-bold font-xs"><?= Ferrymen::getAfd(
                                Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                UploadingFilesMeanings::ТИП_КОНТЕНТА_СТС_ЛИЦЕВАЯ,
                                'hint'
                            ) ?></div>
                            <?= $form->field($model, 'fileStsFace')->fileInput()->label(false) ?>

                        </div>
                        <div class="card-footer px-3 py-2">
                            <?php if (isset($fileStsFace)): ?>
                            <?= Html::a('Скачать <i class="fa fa-cloud-download float-right font-lg"></i>', ['/transport/download-file', 'id' => $fileStsFace['id']], [
                                'class' => 'font-weight-bold font-xs btn-block text-muted',
                            ]) ?>

                            <?php else: ?>
                            <small class="font-xs btn-block text-muted">нет файла</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="card">
                        <div class="card-body p-3 clearfix">
                            <i class="fa fa-camera-retro bg-info p-3 font-2xl mr-3 float-left"></i>
                            <?php if (isset($fileStsReverse)): ?>
                            <div class="h5 text-primary mb-1 mt-2"><?= Html::a(
                                Ferrymen::getAfd(
                                    Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                    UploadingFilesMeanings::ТИП_КОНТЕНТА_СТС_ОБОРОТ,
                                    'title'
                                ),
                                '#',
                                UploadingFilesMeanings::optionsForAttachedFilesLink($fileStsReverse)) ?>

                            </div>
                            <?php else: ?>
                            <div class="h5 text-primary mb-1 mt-2"><?= Ferrymen::getAfd(
                                Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                UploadingFilesMeanings::ТИП_КОНТЕНТА_СТС_ОБОРОТ,
                                'title'
                            ) ?></div>
                            <?php endif; ?>
                            <div class="text-muted text-uppercase font-weight-bold font-xs"><?= Ferrymen::getAfd(
                                Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                UploadingFilesMeanings::ТИП_КОНТЕНТА_СТС_ОБОРОТ,
                                'hint'
                            ) ?></div>
                            <?= $form->field($model, 'fileStsReverse')->fileInput()->label(false) ?>

                        </div>
                        <div class="card-footer px-3 py-2">
                            <?php if (isset($fileStsReverse)): ?>
                            <?= Html::a('Скачать <i class="fa fa-cloud-download float-right font-lg"></i>', ['/transport/download-file', 'id' => $fileStsReverse['id']], [
                                'class' => 'font-weight-bold font-xs btn-block text-muted',
                            ]) ?>

                            <?php else: ?>
                            <small class="font-xs btn-block text-muted">нет файла</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card">
                        <div class="card-body p-3 clearfix">
                            <i class="fa fa-camera-retro bg-info p-3 font-2xl mr-3 float-left"></i>
                            <?php if (isset($fileDk)): ?>
                            <div class="h5 text-primary mb-1 mt-2"><?= Html::a(
                                Ferrymen::getAfd(
                                    Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                    UploadingFilesMeanings::ТИП_КОНТЕНТА_ДИАГНОСТИЧЕСКАЯ_КАРТА,
                                    'title'
                                ),
                                '#',
                                UploadingFilesMeanings::optionsForAttachedFilesLink($fileDk)) ?>

                            </div>
                            <?php else: ?>
                            <div class="h5 text-primary mb-1 mt-2"><?= Ferrymen::getAfd(
                                Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                UploadingFilesMeanings::ТИП_КОНТЕНТА_ДИАГНОСТИЧЕСКАЯ_КАРТА,
                                'title'
                            ) ?></div>
                            <?php endif; ?>
                            <div class="text-muted text-uppercase font-weight-bold font-xs"><?= Ferrymen::getAfd(
                                Ferrymen::fetchAttachedToTransportFilesDescriptions(),
                                UploadingFilesMeanings::ТИП_КОНТЕНТА_ДИАГНОСТИЧЕСКАЯ_КАРТА,
                                'hint'
                            ) ?></div>
                            <?= $form->field($model, 'fileDk')->fileInput()->label(false) ?>

                        </div>
                        <div class="card-footer px-3 py-2">
                            <?php if (isset($fileDk)): ?>
                            <?= Html::a('Скачать <i class="fa fa-cloud-download float-right font-lg"></i>', ['/transport/download-file', 'id' => $fileDk['id']], [
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
        </div>
        <div class="card-footer text-muted">
            <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Транспорт', ['/transport'], ['class' => 'btn btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

            <?php if ($model->isNewRecord): ?>
            <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
            <?php else: ?>
            <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
            <?php endif; ?>

        </div>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<?php
$urlPreview = Url::to(['/transport/preview-file']);

$this->registerJs(<<<JS
// Обработчик щелчка по ссылкам в колонке "Наименование" в таблице файлов.
//
function previewFileOnClick() {
    id = $(this).attr("data-id");
    if (id != "") {
        $("#modal_body_preview").html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#mwPreview").modal();
        $("#modal_body_preview").load("$urlPreview?id=" + id);
    }

    return false;
} // previewFileOnClick()

$(document).on("click", "a[id ^= 'previewFile']", previewFileOnClick);
JS
, yii\web\View::POS_READY);
?>
