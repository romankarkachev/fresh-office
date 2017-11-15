<?php

use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use common\models\AuthItem;
use common\models\User;

/* @var yii\widgets\ActiveForm $form */
/* @var \common\models\User $user */

$formName = strtolower($user->formName());
?>

<?= $form->field($user, 'fo_id')->widget(Select2::className(), [
    'initValueText' => User::getFreshOfficeManagerName($user->fo_id),
    'theme' => Select2::THEME_BOOTSTRAP,
    'language' => 'ru',
    'options' => ['placeholder' => 'Введите имя пользователя Fresh Office'],
    'pluginOptions' => [
        'minimumInputLength' => 1,
        'language' => 'ru',
        'ajax' => [
            'url' => Url::to(['/users/fresh-office-managers-list']),
            'delay' => 500,
            'dataType' => 'json',
            'data' => new JsExpression('function(params) { return {q:params.term}; }')
        ],
        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
        'templateResult' => new JsExpression('function(result) { return result.text; }'),
        'templateSelection' => new JsExpression('function (result) {
    if (!result.id) {return result.text;}
    $("#' . $formName . '-name" ).val(result.text);
    return result.text;
}'),
    ],
]) ?>

<?= $form->field($user, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Введите имя']) ?>

<?= $form->field($user, 'username')->textInput(['maxlength' => 255, 'placeholder' => 'Используется для авторизации в системе']) ?>

<?= $form->field($user, 'email')->textInput(['maxlength' => 255, 'placeholder' => 'Используется для авторизации в системе']) ?>

<?= $form->field($user, 'role_id')->widget(Select2::className(), [
    'data' => AuthItem::arrayMapForSelect2(),
    'theme' => Select2::THEME_BOOTSTRAP,
    'options' => ['placeholder' => '- выберите роль -'],
    'hideSearch' => true,
]); ?>

<?= $form->field($user, 'password')->passwordInput(['placeholder' => 'Минимум 6 символов']) ?>

<?= $form->field($user, 'password_confirm')->passwordInput(['placeholder' => 'Подтвердите пароль']) ?>
