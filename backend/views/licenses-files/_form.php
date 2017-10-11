<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\Organizations;

/* @var $this yii\web\View */
/* @var $model common\models\LicensesFiles */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="licenses-files-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'fkkosTextarea')->textarea(['rows' => 10, 'placeholder' => 'Введите коды ФККО по одному на строку']) ?>

        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'tpFkkos', ['template' => "{error}"])->staticControl() ?>

        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'organization_id')->widget(Select2::className(), [
                'data' => Organizations::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'importFile')->fileInput() ?>

        </div>
    </div>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Сканы лицензий', ['/licenses-files'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
