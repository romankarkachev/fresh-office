<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\Organizations;

/* @var $this yii\web\View */
/* @var $model common\models\PrintEnvelopeForm */

$this->title = 'Печать конверта к пакету корреспонденции | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = ['label' => 'Пакеты корреспонденции', 'url' => ['/correspondence-packages']];
$this->params['breadcrumbs'][] = 'Печать конверта *';
?>
<div class="correspondence-packages-compose-envelope">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'org_id')->widget(Select2::className(), [
                'data' => Organizations::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
            ]) ?>

        </div>
    </div>
    <?= $form->field($model, 'cp_id')->hiddenInput()->label(false) ?>

    <?= Html::submitButton('Сформировать', ['class' => 'btn btn-success btn-lg']) ?>

    <?php ActiveForm::end(); ?>

</div>
