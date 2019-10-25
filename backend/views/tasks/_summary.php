<?php
/* @var $this yii\web\View */
/* @var $model common\models\foTasks */
/* @var $form yii\bootstrap\ActiveForm */
?>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="control-label">Контрагент</label>
            <p>
                <?= $model->companyName ?>

            </p>
        </div>
        <div class="form-group">
            <label class="control-label">Контактное лицо</label>
            <p>
                <?= $model->contactPersonName ?>

            </p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="control-label">Начало</label>
            <p>
                <?= Yii::$app->formatter->asDate($model->DATA_CONTACT, 'php:d.m.Y H:i') ?>

            </p>
        </div>
        <div class="form-group">
            <label class="control-label">Завершение</label>
            <p>
                <?= Yii::$app->formatter->asDate($model->DATA_END_CONTACT, 'php:d.m.Y H:i') ?>

            </p>
        </div>
    </div>
</div>
