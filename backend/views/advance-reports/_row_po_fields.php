<?php

use kartik\select2\Select2;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\MaskedInput;
use common\models\PoEi;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\Po */
/* @var $formModel common\models\AdvanceReportForm */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $counter integer счетчик добавленных авансовых отчетов */

$formName = $formModel->formName();
$formNameId = strtolower($formModel->formName());

$btnDeleteHtml = \yii\helpers\Html::a('<i class="fa fa-times"></i> удалить', '#', [
    'id' => 'btnDeleteRow-' . $counter,
    'class' => 'btn btn-xs btn-danger',
    'data-counter' => $counter,
    'title' => 'Удалить этот авансовый отчет',
]);
?>

    <div id="po-row-<?= $counter ?>" class="row">
        <?php if (Yii::$app->user->can('root') || Yii::$app->user->can('accountant_b')): ?>
        <div class="col-md-2">
            <?= $form->field($model, 'created_by')->widget(Select2::class, [
                'data' => User::arrayMapForSelect2(User::ARRAY_MAP_OF_USERS_BY_ALL_ROLES),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => [
                    'id' => $formNameId . '-created_by-' . $counter,
                    'name' => $formName . '[crudePos][' . $counter . '][created_by]',
                    'placeholder' => '- выберите -',
                ],
            ])->label($model->getAttributeLabel('created_by') . ' ' . $btnDeleteHtml) ?>

        </div>
        <?php endif; ?>
        <div class="col-md-2">
            <?= $form->field($model, 'company_id')->widget(Select2::class, [
                'id' => $formNameId . '-company_id-' . $counter,
                'initValueText' => $model->companyName,
                'theme' => Select2::THEME_BOOTSTRAP,
                'language' => 'ru',
                'options' => [
                    'placeholder' => 'Введите наименование (ИНН, ОГРН)',
                    'id' => $formNameId . '-company_id-' . $counter,
                    'name' => $formName . '[crudePos][' . $counter . '][company_id]',
                ],
                'pluginOptions' => [
                    'minimumInputLength' => 1,
                    'language' => 'ru',
                    'ajax' => [
                        'url' => Url::to(\backend\controllers\CompaniesController::URL_CASTING_AS_ARRAY),
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
        <div class="col-md-3">
            <?= $form->field($model, 'ei_id')->widget(Select2::class, [
                'data' => PoEi::arrayMapByGroupsForSelect2(),
                'theme' => Select2::THEME_BOOTSTRAP,
                'options' => [
                    'id' => $formNameId . '-ei_id-' . $counter,
                    'name' => $formName . '[crudePos][' . $counter . '][ei_id]',
                    'placeholder' => '- выберите -',
                ],
            ]) ?>

        </div>
        <div class="col-md-1">
            <?= $form->field($model, 'amount', [
                'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
            ])->widget(MaskedInput::className(), [
                'options' => ['id' => $formNameId . '-amount-' . $counter],
                'clientOptions' => [
                    'alias' =>  'numeric',
                    'groupSeparator' => ' ',
                    'autoUnmask' => true,
                    'autoGroup' => true,
                    'removeMaskOnSubmit' => true,
                ],
            ])->textInput([
                'id' => $formNameId . '-amount-' . $counter,
                'name' => $formName . '[crudePos][' . $counter . '][amount]',
                'maxlength' => true,
                'placeholder' => '0',
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'comment')->textInput([
                'id' => $formNameId . '-comment-' . $counter,
                'name' => $formName . '[crudePos][' . $counter . '][comment]',
                'placeholder' => 'Введите комментарий',
            ]) ?>

        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'files[]')->fileInput([
                'id' => $formNameId . '-files[]-' . $counter,
                'name' => $formName . '[crudePos][' . $counter . '][files][]',
                'multiple' => true,
            ]) ?>

        </div>
        <?php if (!Yii::$app->user->can('root') && !Yii::$app->user->can('accountant_b')): ?>
        <div class="col-md-1">
            <label class="control-label" for="<?= 'btnDeleteRow-' . $counter ?>">&nbsp;</label>
            <div class="form-group">
                <?= $btnDeleteHtml ?>

            </div>
        </div>
        <?php endif; ?>
    </div>
