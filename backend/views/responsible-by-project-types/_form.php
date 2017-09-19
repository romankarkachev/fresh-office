<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\DirectMSSQLQueries;
use common\models\ProjectsTypes;

/* @var $this yii\web\View */
/* @var $model common\models\ResponsibleByProjectTypes */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="responsible-by-project-types-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'project_type_id')->widget(Select2::className(), [
                'data' => ProjectsTypes::arrayMapForSelect2(DirectMSSQLQueries::PROJECTS_TYPES_FOR_RESPONSIBLE),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <?= $form->field($model, 'receivers')->textarea(['autofocus' => true, 'rows' => 3, 'placeholder' => 'Один почтовый ящик на строку']) ?>

    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> Ответственные по типам проектов', ['/responsible-by-project-types'], ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
            <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>

        <?php else: ?>
            <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
