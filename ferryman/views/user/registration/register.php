<?php

use yii\helpers\Html;
use common\components\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model dektrium\user\models\User */
/* @var $module dektrium\user\Module */
/* @var $invite \common\models\FerrymenInvitations */

$this->title = Yii::t('user', 'Sign up');
$this->params['breadcrumbs'][] = $this->title;

$ferrymanRep = '';
if ($invite->ferryman != null) {
    if ($invite->ferryman->name_short != null && trim($invite->ferryman->name_short) != '')
        $ferrymanRep = $invite->ferryman->name_short;
    else
        $ferrymanRep = $invite->ferryman->name;
    if ($invite->ferryman->inn != null && trim($invite->ferryman->inn) != '') $ferrymanRep .= ', ИНН ' . $invite->ferryman->inn;
}
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mx-4">
                <?php $form = ActiveForm::begin([
                    'id' => 'registration-form',
                    'enableAjaxValidation' => true,
                    'enableClientValidation' => false,
                ]); ?>

                <div class="card-body p-4">
                    <h1>Регистрация</h1>
                    <?php if ($ferrymanRep != ''): ?>
                    <p class="text-muted"><?= $ferrymanRep ?></p>
                    <?php endif; ?>
                    <?= $form->field($model, 'email')->textInput(['autofocus' => true, 'placeholder' => 'Введите свой E-mail']) ?>

                    <?= $form->field($model, 'name')->textInput(['placeholder' => 'Введите имя', 'title' => 'Введите свое имя или наименование организации']) ?>

                    <?php if ($module->enableGeneratingPassword == false): ?>
                    <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Введите пароль']) ?>

                    <?= $form->field($model, 'password_confirm')->passwordInput(['placeholder' => 'Подтвердите пароль']) ?>

                    <?php endif ?>
                    <?= $form->field($model, 'invite_id')->hiddenInput()->label(false) ?>

                </div>
                <div class="card-footer p-4">
                    <div class="row">
                        <div class="col-10">
                            <?= Html::submitButton(Yii::t('user', 'Sign up'), ['class' => 'btn btn-success btn-block']) ?>

                        </div>
                        <div class="col-2">
                            <?= Html::a('<i class="fa fa-sign-in" aria-hidden="true"></i>', ['/login'], ['class' => 'btn btn-secondary btn-block', 'title' => 'Авторизоваться']) ?>

                        </div>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>
