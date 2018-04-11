<?php

use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\AssignFerrymanForm|common\models\FerrymanOrderForm */
/* @var $form yii\bootstrap\ActiveForm */
?>

        <div class="col-md-6">
            <?= $form->field($model, 'driver_id')->widget(Select2::className(), [
                'data' => $model->ferryman->arrayMapOfDriversForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'transport_id')->widget(Select2::className(), [
                'data' => $model->ferryman->arrayMapOfTransportForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
