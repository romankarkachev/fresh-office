<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model cemail\models\UserAccessForm */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Управление доступом | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Управление доступом';

$urlUserMailboxes = Url::to(['/users-access/render-user-mailboxes']);

$formName = $model->formName();
?>
<div class="users-access">
    <div class="card">
        <div class="card-block">
            <?php $form = ActiveForm::begin(); ?>

            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'user_id')->widget(Select2::className(), [
                        'data' => User::arrayMapForSelect2(User::ARRAY_MAP_OF_USERS_BY_ALL_ROLES),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                        'pluginEvents' => [
                            'change' => new JsExpression('function() {
    $block = $("#block-mailboxes");
    $block.html("<p><i class=\"fa fa-cog fa-spin fa-2x text-muted\"></i><span class=\"sr-only\">Подождите...</span></p>");
    $block.load("' . $urlUserMailboxes . '?user_id=" + $(this).val());
}'),
                        ],
                    ]) ?>

                </div>
            </div>
            <div id="block-mailboxes"></div>
        </div>
        <div class="card-footer text-muted">
            <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-info btn-lg']) ?>

        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
var checked = false;

// Обработчик щелчка по ссылке "Отметить все документы".
//
function toggleCheckedOnClick() {
    if (checked) {
        operation = "uncheck";
        checked = false;
    }
    else {
        operation = "check";
        checked = true;
    }

    $("input[name ^= '$formName']").iCheck(operation);

    return false;
} // toggleCheckedOnClick()

$(document).on("click", "#toggleChecked", toggleCheckedOnClick);
JS
, yii\web\View::POS_READY);
?>
