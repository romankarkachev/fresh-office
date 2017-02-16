<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\Offices;

/* @var yii\web\View $this */
/* @var dektrium\user\models\User $user */
/* @var dektrium\user\models\Profile $profile */
?>

<?php $this->beginContent('@backend/views/user/admin/update.php', ['user' => $user]) ?>

<?php $form = ActiveForm::begin([
    'layout' => 'horizontal',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'wrapper' => 'col-sm-9',
        ],
    ],
]); ?>

<?= $form->field($profile, 'surname') ?>

<?= $form->field($profile, 'name') ?>

<?= $form->field($profile, 'patronymic') ?>

<?= $form->field($profile, 'phone') ?>

<?= $form->field($profile, 'office_id')->widget(Select2::className(), [
    'data' => Offices::arrayMapForSelect2(),
    'theme' => Select2::THEME_BOOTSTRAP,
    'options' => ['placeholder' => '- выберите -'],
    'hideSearch' => true,
]) ?>

<div class="form-group">
    <div class="col-lg-offset-3 col-lg-9">
        <?= Html::submitButton(Yii::t('user', 'Update'), ['class' => 'btn btn-block btn-success']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php $this->endContent() ?>
