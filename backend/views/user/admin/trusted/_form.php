<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use backend\controllers\UsersController;

use common\models\User;
use common\models\UsersTrusted;

/* @var $this yii\web\View */
/* @var $model \common\models\UsersTrusted */
/* @var $userProfileName string имя пользователя */

$formTitle = 'Форма добавления';
if (!empty($userProfileName)) {
    $formTitle .= ' доверенных лиц пользователя <strong>' . $userProfileName . '</strong>';
}
?>

<?php $form = ActiveForm::begin([
    'id' => UsersTrusted::DOM_IDS['PJAX_FORM_ID'],
    'action' => Url::to(UsersController::URL_CREATE_TRUSTED_OTF_AS_ARRAY),
    'options' => ['data-pjax' => true],
]); ?>

<div class="panel panel-success">
    <div class="panel-heading"><?= $formTitle ?></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-2">
                <?= $form->field($model, 'section')->widget(Select2::class, [
                    'data' => UsersTrusted::arrayMapForSelect2(),
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'size' => Select2::SMALL,
                    'options' => [
                        'placeholder' => '- выберите раздел -',
                        'title' => 'Выберите раздел учета, к которому будет применяться доверение',
                    ],
                    'hideSearch' => true,
                ]) ?>

            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'trusted_id')->widget(Select2::class, [
                    'data' => User::arrayMapForSelect2(User::ARRAY_MAP_OF_ALL_EXCEPT_SPECIAL, $model->user_id),
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'size' => Select2::SMALL,
                    'options' => [
                        'placeholder' => '- выберите пользователя -',
                        'title' => 'Выберите пользователя, которому станут доступны элементы выбранного раздела (при условии, что создатель или ответственный в них - редактируемый пользователь)',
                    ],
                ]) ?>

            </div>
            <div class="col-md-2">
                <div class="form-group" style="margin-bottom: 0px;"><label class="control-label">&nbsp;</label></div>
                <?= Html::submitButton('Добавить <i class="fa fa-arrow-down"></i> ', ['class' => 'btn btn-success btn-xs']) ?>

            </div>
        </div>
        <?= $form->field($model, 'user_id', ['template' => '{input}', 'options' => ['tag' => false]])->hiddenInput()->label(false) ?>

    </div>
</div>
<?php ActiveForm::end(); ?>
