<?php

use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\TransportRequests */
/* @var $form yii\bootstrap\ActiveForm */
?>

        <div class="col-md-2">
            <?= $form->field($model, 'city_id')->widget(Select2::className(), [
                'data' => $model->arrayMapOfCitiesForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
