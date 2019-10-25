<?php

use common\models\UploadingFilesMeanings;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \common\models\StorageBulkImportForm */
/* @var $form yii\bootstrap\ActiveForm */

$this->title = 'Массовый импорт файлов в хранилище | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Массовый импорт';

$formName = $model->formName();
$formNameId = strtolower($formName);
?>
<div class="storage-bulk-import-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'type_id')->widget(Select2::class, [
                'data' => UploadingFilesMeanings::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <div class="form-group">
        <p>Соберите все необходимые файлы в одном месте, нажмите на кнопку и единоразово отметьте все файлы. Вы можете прикрепить до <strong><?= !empty($model->rules()[3]['maxFiles']) ? $model->rules()[3]['maxFiles'] : 100 ?></strong> файлов.</p>
        <p>
            Имена файлов должны представлять собой идентификаторы проектов для того, чтобы процесс прошел в соответствии с задумкой.
            В случае, если проект не будет обнаружен, такой файл будет пропущен с уведомлением об этом. Идентификатор
            проекта обязателен, поскольку по нему определяется контрагент (обязательное поле для хранилища).
        </p>
        <?= $form->field($model, 'files[]')->fileInput(['multiple' => true]) ?>

    </div>
    <p>
        <?= Html::submitButton('Выполнить', ['class' => 'btn btn-default btn-lg', 'title' => 'Поместить файлы в хранилище']) ?>

    </p>
    <?php ActiveForm::end(); ?>

</div>
