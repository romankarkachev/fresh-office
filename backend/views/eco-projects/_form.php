<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use common\models\EcoTypes;
use common\models\User;
use backend\controllers\EcoProjectsController;

/* @var $this yii\web\View */
/* @var $model common\models\EcoProjects */
/* @var $form yii\bootstrap\ActiveForm */

$formName = $model->formName();
$formNameId = strtolower($formName);
?>

<div class="eco-projects-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'date_start')->widget(DateControl::className(), [
                'value' => $model->date_start,
                'type' => DateControl::FORMAT_DATE,
                'displayFormat' => 'php:d.m.Y',
                'saveFormat' => 'php:Y-m-d',
                'widgetOptions' => [
                    'layout' => '{input}{picker}',
                    'options' => ['placeholder' => '- выберите дату -'],
                    'pluginOptions' => [
                        'weekStart' => 1,
                        'autoclose' => true,
                    ],
                ],
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'type_id')->widget(Select2::className(), [
                'data' => EcoTypes::arrayMapForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
                'hideSearch' => true,
                'pluginOptions' => ['allowClear' => true],
                'pluginEvents' => [
                    'select2:select' => new JsExpression('function() {
    typeOnChange();
}'),
                ],
            ]) ?>

        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'ca_id')->widget(Select2::className(), [
                'initValueText' => \common\models\TransportRequests::getCustomerName($model->ca_id),
                'theme' => Select2::THEME_BOOTSTRAP,
                'language' => 'ru',
                'options' => ['placeholder' => 'Введите наименование'],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 1,
                    'language' => 'ru',
                    'ajax' => [
                        'url' => Url::to(['projects/direct-sql-counteragents-list']),
                        'delay' => 500,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(result) { return result.text; }'),
                    'templateSelection' => new JsExpression('function (result) { return result.text; }'),
                ],
            ]) ?>

        </div>
        <?php if (Yii::$app->user->can('root') || Yii::$app->user->can('ecologist_head')): ?>
        <div class="col-md-2">
            <?= $form->field($model, 'created_by')->widget(Select2::className(), [
                'data' => User::arrayMapForSelect2(User::ARRAY_MAP_OF_USERS_BY_ECOLOGIST_ROLE),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => ['placeholder' => '- выберите -'],
            ]) ?>

        </div>
        <?php endif; ?>
    </div>
    <div id="block-milestones" class="form-group"></div>
    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите произвольный комментарий']) ?>

    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . EcoProjectsController::ROOT_LABEL, EcoProjectsController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?php if ($model->isNewRecord): ?>
        <?= Html::submitButton('<i class="fa fa-plus-circle" aria-hidden="true"></i> Создать', ['class' => 'btn btn-success btn-lg']) ?>
        <?php else: ?>
        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>
        <?php endif; ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<?php
$urlBlockMilestones = Url::to(['/eco-projects/render-milestones-block']);
$this->registerJs(<<<JS

// Функция выполняет загрузку через ajax блока с планируемыми этапами работы.
//
function typeOnChange() {
    type_id = $("#$formNameId-type_id").val();
    date_start = $("#$formNameId-date_start").val();
    if (type_id != "" && type_id != undefined && date_start != "" && date_start != undefined) {
        \$block = $("#block-milestones");
        \$block.html("<p class=\"text-center\"><i class=\"fa fa-cog fa-spin fa-2x text-muted\"></i></p>");
        \$block.load("$urlBlockMilestones?type_id=" + type_id + "&date_start=" + date_start);
    }
} // typeOnChange()

// плагин bootstrap-datepicker не предоставляет адекватной работы с датой (встроенный механизм plugin-events отдает старую дату)
$("#$formNameId-date_start").on("change", function() {
    typeOnChange();
});
JS
, \yii\web\View::POS_READY);
?>
