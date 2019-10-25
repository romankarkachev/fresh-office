<?php

use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\Tenders */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $licenceRequestsFiltered array отобранные запросы лицензий */
?>
    <?= $form->field($model, 'lr_id')->widget(Select2::className(), [
        'data' => $licenceRequestsFiltered,
        'theme' => Select2::THEME_BOOTSTRAP,
        'options' => ['placeholder' => '- доступные запросы лицензий -'],
        'hideSearch' => true,
    ])->label('Вы можете дополнить табличную часть запросом лицензий:') ?>
