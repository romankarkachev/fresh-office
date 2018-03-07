<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\MaskedInput;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use common\models\User;

/* @var yii\web\View $this */
/* @var common\models\User $user */
/* @var common\models\Profile $profile */
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

<?= $form->field($profile, 'name') ?>

<?= $form->field($profile, 'fo_id')->widget(Select2::className(), [
    'initValueText' => User::getFreshOfficeManagerName($profile->fo_id),
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
        'templateSelection' => new JsExpression('function (result) { return result.text; }'),
    ],
]) ?>

<?= $form->field($profile, 'limit_cp_me')->widget(MaskedInput::className(), [
    'clientOptions' => ['alias' =>  'numeric'],
])->textInput([
    'maxlength' => true,
    'placeholder' => '0',
    'title' => 'Менеджер не сможет выбрать способ доставки Major Express, если с 1 числа текущего месяца будет достигнут этот предел',
]) ?>

<?= $form->field($profile, 'notify_when_cp')->checkbox([], false)->label(null, ['title' => 'Уведомлять по E-mail при создании пакета корреспонденции']) ?>

<div class="form-group field-profile-role">
    <label class="control-label col-sm-3" for="profile-role"><?= $profile->user->getAttributeLabel('role_id') ?></label>
    <div class="col-sm-9">
        <input type="text" id="profile-role" class="form-control" value="<?= $profile->user->getRoleDescription() ?>" readonly>
    </div>
</div>
<div class="form-group">
    <div class="col-lg-offset-3 col-lg-9">
        <?= Html::submitButton(Yii::t('user', 'Update'), ['class' => 'btn btn-block btn-success']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$this->registerJs(<<<JS
$("input").iCheck({
    checkboxClass: "icheckbox_square-green"
});
JS
    , yii\web\View::POS_READY);
?>

<?php $this->endContent() ?>
