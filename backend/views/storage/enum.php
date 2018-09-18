<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use backend\components\grid\GridView;
use yii\web\JsExpression;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $searchModel common\models\FileStorageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = HtmlPurifier::process('Сбор файлов &mdash; Файловое хранилище | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Файловое хранилище', 'url' => ['/storage']];
$this->params['breadcrumbs'][] = 'Сбор файлов';
?>
<div class="enum-file-storage-list">
    <?php $form = ActiveForm::begin(['action' => Url::to(['storage/store-enumerated-files'])]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'checkboxOptions' => function ($model, $key, $index, $column) {
                    return ['value' => $model['counter']];
                },
                'options' => ['width' => '30'],
                'visible' => false,
            ],
            [
                'header' => 'Контрагент',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    return '<p>Папка: ' . $model['folderName'] . '</p>' . Select2::widget([
                        'initValueText' => $model['caName'],
                        'value' => $model['caId'],
                        'name' => 'CounteragentsFolders[' . $model['counter'] . '][ca_id]',
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'language' => 'ru',
                        'options' => [
                            'data-counter' => $model['counter'],
                            'placeholder' => 'Введите наименование',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 1,
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

counter = $("#" + result.element.parentElement.id).attr("data-counter");
if (counter != "") {
    $("input[id ^= \'enumfiles-ca_id-" + counter + "\']").val(result.id);
    $("input[id ^= \'enumfiles-ca_name-" + counter + "\']").val(result.text);
}

return result.text;
}'),
                        ],
                    ]);
                },
                'options' => ['width' => '500'],
            ],
            [
                'header' => 'Файлы',
                'format' => 'raw',
                'value' => function($model, $key, $index, $column) {
                    $result = '';
                    foreach ($model['files'] as $file) {
                        $result .= '
                        <div class="row">
                            <div class="col-md-9">' . $file['relativeFilePath'] . '</div>
                            <div class="col-md-3">' . Select2::widget([
                                'name' => 'EnumFiles[' . $model['counter'] . $file['number'] . '][type_id]',
                                'value' => $file['type_id'],
                                'data' => \common\models\UploadingFilesMeanings::arrayMapForSelect2(),
                                'size' => Select2::SMALL,
                                'theme' => Select2::THEME_BOOTSTRAP,
                                'options' => ['placeholder' => '- выберите -'],
                                'hideSearch' => true,
                            ]) .
                            Html::hiddenInput('EnumFiles[' . $model['counter'] . $file['number'] . '][folder_name]', $model['folderName']) .
                            Html::hiddenInput('EnumFiles[' . $model['counter'] . $file['number'] . '][fn]', $file['fn']) .
                            Html::hiddenInput('EnumFiles[' . $model['counter'] . $file['number'] . '][ffp]', $file['ffp']) .
                            Html::hiddenInput('EnumFiles[' . $model['counter'] . $file['number'] . '][ca_id]', $model['caId'], ['id' => 'enumfiles-ca_id-' . $model['counter'] . $file['number']]) .
                            Html::hiddenInput('EnumFiles[' . $model['counter'] . $file['number'] . '][ca_name]', $model['caName'], ['id' => 'enumfiles-ca_name-' . $model['counter'] . $file['number']]) .
                            '
                            </div>
                        </div>';
                    }

                    return $result;
                },
            ],
        ],
    ]); ?>

    <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Завершить', ['class' => 'btn btn-success btn-lg']) ?>

    <?php ActiveForm::end(); ?>

</div>
