<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\UploadingFilesMeanings;

/* @var $this yii\web\View */
/* @var $model common\models\FileStorageSearch */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $searchApplied bool */
?>

<div class="file-storage-search">
    <?php $form = ActiveForm::begin([
        'action' => ['/storage'],
        'method' => 'get',
        'options' => ['id' => 'frm-search', 'class' => ($searchApplied ? 'collapse in' : 'collapse')],
    ]); ?>

    <div class="panel panel-info">
        <div class="panel-heading">Форма отбора</div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'type_id')->widget(Select2::className(), [
                        'data' => UploadingFilesMeanings::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginOptions' => ['allowClear' => true],
                        'hideSearch' => true,
                    ]) ?>

                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Выполнить', ['class' => 'btn btn-info']) ?>

        <?= Html::a('Отключить отбор', ['/storage'], ['class' => 'btn btn-default']) ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
