<?php

use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\PaymentOrders */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $dataSet array */
?>

            <?= $form->field($model, 'pd_id')->widget(Select2::className(), [
                'data' => $dataSet,
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>
