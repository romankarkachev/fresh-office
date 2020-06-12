<?php

use kartik\select2\Select2;
use common\models\EcoProjects;

/* @var $this yii\web\View */
/* @var $model common\models\Po */
/* @var $form yii\bootstrap\ActiveForm */
?>

<?= $form->field($model, 'ep')->widget(Select2::class, [
    'data' => EcoProjects::arrayMapForSelect2(),
    'theme' => Select2::THEME_BOOTSTRAP,
    'options' => ['placeholder' => '- выберите -'],
]) ?>
