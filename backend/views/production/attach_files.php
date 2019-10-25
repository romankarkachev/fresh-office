<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \common\models\ProductionAttachFilesForm */
/* @var $form yii\bootstrap\ActiveForm */

$this->title = 'Прикрепление файлов к проектам по производству | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Прикрепление файлов к проектам';

$formName = $model->formName();
$formNameId = strtolower($formName);
?>
<div class="production-feedback-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <div class="form-group field-<?= $formNameId ?>-project_id">
                <label class="control-label" for="<?= $formNameId ?>-project_id">ID проекта</label>
                <?= Html::input('text', $formName . '[project_id]', '', [
                    'id' => $formNameId . '-project_id',
                    'class' => 'form-control',
                    'placeholder' => 'Введите ID проекта',
                ]) ?>

                <p class="help-block help-block-error"></p>
            </div>
        </div>
    </div>
    <div class="form-group">
        <p>Соберите все необходимые файлы в одном месте, нажмите на кнопку и единоразово отметьте все файлы. Вы можете прикрепить до <strong>100</strong> файлов.</p>
        <?= $form->field($model, 'files[]')->fileInput(['multiple' => true]) ?>

    </div>
    <p>
        <?= Html::submitButton('<i class="fa fa-plane" aria-hidden="true"></i> Отправить', [
            'class' => 'btn btn-default btn-lg',
            'title' => 'Выполнить отправку файлов производства на сервер',
        ]) ?>

    </p>
    <?php ActiveForm::end(); ?>

</div>
