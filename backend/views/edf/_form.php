<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use kartik\datecontrol\DateControl;
use yii\web\View;
use yii\widgets\MaskedInput;
use backend\controllers\EdfController;
use common\models\User;
use common\models\Organizations;
use common\models\TransportRequests;
use common\models\EdfStates;

/* @var $this yii\web\View */
/* @var $model common\models\Edf */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $tp common\models\EdfTp[]|\yii\data\ActiveDataProvider */
/* @var $hasAccess bool наличие доступа к нескольким объектам электронного документа (менеджер не имеет) */

$modelId = !empty($model->id) ? $model->id : '0';
$formName = strtolower($model->formName());
$label_bank_bik = $model->attributeLabels()['req_bik'];
$label_inn = $model->attributeLabels()['req_inn'];
$label_ogrn = $model->attributeLabels()['req_ogrn'];
$add_row_prompt = '<p class="text-muted">Табличная часть пуста.</p>';
$promptEmptyInn = '<p class="text-muted">Для автоматического заполнения реквизитов, введите ИНН, КПП, ОГРН, наименование, адрес или директора контрагента.</p>';
if (is_array($tp)) {
    $count = count($tp);
}
else {
    $count = $tp->getTotalCount();
}

// если есть ошибки в модели, то некоторые из них могут быть не видны пользователю, потому что расположены на разных вкладках
// поэтому добавим значок к заголовку вкладки, если на ней расположены проблемные поля
$modelHasErrorsIcon = ' <i class="fa fa-exclamation-triangle text-warning" aria-hidden="true" title="На этой вкладке есть поля, заполненные неверно"></i>';
$warningIcons = [
    'req' => '',
    'tp' => '',
];
$tabReqKeys = [
    'req_inn',
    'req_bik',
    'req_an',
    'req_ca',
    'req_bn',
];
$tabTpKeys = [
    'tpErrors',
];
if (!array_diff_key(array_flip($tabReqKeys), $model->errors)) {
    $warningIcons['req'] = $modelHasErrorsIcon;
}
if (!array_diff_key(array_flip($tabTpKeys), $model->errors)) {
    $warningIcons['tp'] = $modelHasErrorsIcon;
}
?>

<div class="edf-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="panel with-nav-tabs panel-success">
        <div class="panel-heading">
            <ul class="nav nav-pills" role="tablist">
                <li role="presentation" class="active"><a href="#common" aria-controls="common" role="tab" data-toggle="tab">Общие</a></li>
                <li role="presentation"><a href="#req" aria-controls="req" role="tab" data-toggle="tab">Реквизиты<?= $warningIcons['req'] ?></a></li>
                <li role="presentation"><a href="#tp" aria-controls="tp" role="tab" data-toggle="tab">Отходы<?= (isset($count) && $count > 0) ? ' (' . $count . ')' : '' ?><?= $warningIcons['tp'] ?></a></li>
            </ul>
        </div>
        <div class="panel-body">
            <div class="tab-content" style="padding: 10px;">
                <div role="tabpanel" class="tab-pane fade in active" id="common">
                    <div class="row">
                        <div class="col-md-2">
                            <?= $form->field($model, 'type_id')->widget(Select2::className(), [
                                'data' => \common\models\DocumentsTypes::arrayMapForSelect2(),
                                'theme' => Select2::THEME_BOOTSTRAP,
                                'options' => ['placeholder' => '- выберите -'],
                                'hideSearch' => true,
                                'disabled' => ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК && $model->state_id != EdfStates::STATE_ОТКАЗ) || ($model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА && $model->state_id != EdfStates::STATE_ОТКАЗ),
                            ]) ?>

                        </div>
                        <?php if (!empty($model->org_id)): ?>
                        <?= $this->render('_field_dt', ['model' => $model, 'form' => $form, 'hasAccess' => $hasAccess]) ?>

                        <?php else: ?>
                        <div id="block-type"></div>
                        <?php endif; ?>
                        <div class="col-md-2">
                            <?= $form->field($model, 'org_id')->widget(Select2::className(), [
                                'data' => Organizations::arrayMapForSelect2(),
                                'theme' => Select2::THEME_BOOTSTRAP,
                                'options' => ['placeholder' => '- выберите -'],
                                'hideSearch' => true,
                                'disabled' => ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК && $model->state_id != EdfStates::STATE_ОТКАЗ) || ($model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА && $model->state_id != EdfStates::STATE_ОТКАЗ),
                            ]) ?>

                        </div>
                        <?php if (!empty($model->org_id)): ?>
                        <?= $this->render('_fields_org', ['model' => $model, 'form' => $form, 'hasAccess' => $hasAccess]) ?>

                        <?php else: ?>
                        <div id="block-ba"></div>
                        <?php endif; ?>
                        <div class="col-md-2">
                            <?= $form->field($model, 'doc_date')->widget(DateControl::className(), [
                                'value' => $model->doc_date,
                                'type' => DateControl::FORMAT_DATE,
                                'displayFormat' => 'php:d.m.Y',
                                'saveFormat' => 'php:Y-m-d',
                                'widgetOptions' => [
                                    'layout' => '{input}{picker}',
                                    'options' => [
                                        'placeholder' => '- выберите дату -',
                                        'disabled' => (Yii::$app->user->can('root') || Yii::$app->user->can('operator_head') ? false : ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК && $model->state_id != EdfStates::STATE_ОТКАЗ) || ($model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА && $model->state_id != EdfStates::STATE_ОТКАЗ)),
                                        'autocomplete' => 'off',
                                    ],
                                    'pluginOptions' => [
                                        'weekStart' => 1,
                                        'autoclose' => true,
                                    ],
                                    'pluginEvents' => [
                                        //'changeDate' => 'function(e) { dateClosePlanOnChange(e, ' . $model->id . ', e.format("yyyy-mm-dd")); }',
                                    ],
                                ],
                            ]) ?>

                        </div>
                    </div>
                    <div class="row">
                        <?php if (!$hasAccess): ?>
                        <div class="col-md-2">
                            <?= $form->field($model, 'doc_date_expires')->widget(DateControl::className(), [
                                'value' => $model->doc_date_expires,
                                'type' => DateControl::FORMAT_DATE,
                                'displayFormat' => 'php:d.m.Y',
                                'saveFormat' => 'php:Y-m-d',
                                'widgetOptions' => [
                                    'layout' => '{input}{picker}',
                                    'options' => ['placeholder' => '- выберите дату -', 'disabled' => ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК && $model->state_id != EdfStates::STATE_ОТКАЗ) || ($model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА && $model->state_id != EdfStates::STATE_ОТКАЗ)],
                                    'pluginOptions' => [
                                        'weekStart' => 1,
                                        'autoclose' => true,
                                    ],
                                ],
                            ]) ?>

                        </div>
                        <div class="col-md-2">
                            <?= $form->field($model, 'amount', [
                                'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
                            ])->widget(MaskedInput::className(), [
                                'clientOptions' => [
                                    'alias' =>  'numeric',
                                    'groupSeparator' => ' ',
                                    'autoUnmask' => true,
                                    'autoGroup' => true,
                                    'removeMaskOnSubmit' => true,
                                ],
                            ])->textInput([
                                'maxlength' => true,
                                'placeholder' => '0',
                                'title' => 'Сумма договора',
                            ]) ?>

                        </div>
                        <div class="col-md-2">
                            <?= $form->field($model, 'manager_id')->widget(Select2::className(), [
                                'data' => User::arrayMapForSelect2(User::ARRAY_MAP_OF_USERS_BY_ALL_ROLES),
                                'theme' => Select2::THEME_BOOTSTRAP,
                                'options' => ['placeholder' => '- выберите -'],
                            ]) ?>

                        </div>
                        <?php else: ?>
                        <?= $form->field($model, 'doc_date_expires')->hiddenInput()->label(false) ?>

                        <?= $form->field($model, 'manager_id')->hiddenInput()->label(false) ?>

                        <?php endif; ?>
                        <div class="col-md-3">
                            <?= $form->field($model, 'fo_ca_id')->widget(Select2::className(), [
                                'initValueText' => TransportRequests::getCustomerName($model->fo_ca_id),
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
                                'pluginEvents' => [
                                    'select2:select' => new JsExpression('function() {
    typeOnChange();
}'),
                                ],
                                'disabled' => ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК && $model->state_id != EdfStates::STATE_ОТКАЗ) || ($model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА && $model->state_id != EdfStates::STATE_ОТКАЗ),
                            ]) ?>

                        </div>
                        <?php if (!$model->isNewRecord): ?>
                        <div class="col-md-1">
                            <label for="<?= $formName ?>-is_received_scan" class="control-label">Скан</label>
                            <?= $form->field($model, 'is_received_scan')->checkbox(['disabled' => $hasAccess])->label(false) ?>

                        </div>
                        <div class="col-md-1">
                            <label for="<?= $formName ?>-is_received_original" class="control-label">Оригинал</label>
                            <?= $form->field($model, 'is_received_original')->checkbox(['disabled' => $hasAccess])->label(false) ?>

                        </div>
                        <?php endif; ?>
                        <div class="col-md-1">
                            <label for="<?= strtolower($model->formName()) ?>-is_typical_form" class="control-label">Типовой</label>
                            <?= $form->field($model, 'is_typical_form')->checkbox()->label(false) ?>

                        </div>
                        <?= $form->field($model, 'req_inn')->hiddenInput()->label(false) ?>

                        <?= $form->field($model, 'req_kpp')->hiddenInput()->label(false) ?>

                        <?= $form->field($model, 'req_ogrn')->hiddenInput()->label(false) ?>

                        <?= $form->field($model, 'req_name_full')->hiddenInput()->label(false) ?>

                        <?= $form->field($model, 'req_name_short')->hiddenInput()->label(false) ?>

                        <?= $form->field($model, 'req_address_j')->hiddenInput()->label(false) ?>

                        <?= $form->field($model, 'req_dir_post')->hiddenInput()->label(false) ?>

                        <?= $form->field($model, 'req_dir_name')->hiddenInput()->label(false) ?>

                        <?= $form->field($model, 'req_dir_name_of')->hiddenInput()->label(false) ?>

                        <?= $form->field($model, 'req_dir_name_short')->hiddenInput()->label(false) ?>

                        <?= $form->field($model, 'req_dir_name_short_of')->hiddenInput()->label(false) ?>

                        <?= $form->field($model, 'basis')->hiddenInput()->label(false) ?>

                    </div>
                    <?= $form->field($model, 'comment')->textarea(['rows' => 10, 'placeholder' => 'Введите комментарий']) ?>

                    <?php if (Yii::$app->user->can('root')): ?>
                    <?= $form->field($model, 'state_id')->widget(Select2::class, [
                        'data' => EdfStates::arrayMapForSelect2(),
                        'theme' => Select2::THEME_BOOTSTRAP,
                        'options' => ['placeholder' => '- выберите -'],
                    ]) ?>

                    <?php else: ?>
                    <?= $form->field($model, 'state_id')->hiddenInput()->label(false) ?>

                    <?php endif; ?>
                </div><!-- common -->
                <div role="tabpanel" class="tab-pane fade" id="req">
                    <div class="panel panel-info">
                        <?php if ($model->isNewRecord): ?>
                        <div class="panel-heading">Информация о контрагенте</div>
                        <?php endif; ?>
                        <div class="panel-body">
                            <?php if ($model->isNewRecord): ?>
                            <?= $promptEmptyInn ?>

                            <div class="form-group">
                                <?= Html::input('text', $model->formName() . '[dadataCasting]', null, [
                                    'id' => 'dadataCasting',
                                    'class' => 'form-control',
                                    'placeholder' => 'Мастер подбора контрагентов',
                                    'title' => 'Универсальный подбор и автозаполнение реквизитов контрагентов',
                                    'autofocus' => true,
                                ]) ?>

                            </div>
                            <div id="block-reqs"></div>
                            <?php else: ?>
                                <?= $model->getCounteragentHtmlRep() ?>

                                <div class="row">
                                    <div class="col-md-3">
                                        <?= $form->field($model, 'req_address_f')->textInput(['placeholder' => 'Введите фактический адрес', 'disabled' => ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК && $model->state_id != EdfStates::STATE_ОТКАЗ) || ($model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА && $model->state_id != EdfStates::STATE_ОТКАЗ && (Yii::$app->user->can('operator_head') && $model->state_id >= EdfStates::STATE_ОТПРАВЛЕН))]) ?>

                                    </div>
                                    <div class="col-md-2">
                                        <?= $form->field($model, 'req_phone')->textInput(['maxlength' => true, 'placeholder' => 'Введите телефоны', 'disabled' => ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК && $model->state_id != EdfStates::STATE_ОТКАЗ) || ($model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА && $model->state_id != EdfStates::STATE_ОТКАЗ && (Yii::$app->user->can('operator_head') && $model->state_id >= EdfStates::STATE_ОТПРАВЛЕН))]) ?>

                                    </div>
                                    <div class="col-md-2">
                                        <?= $form->field($model, 'req_email')->textInput(['maxlength' => true, 'placeholder' => 'Введите E-mail', 'disabled' => ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК && $model->state_id != EdfStates::STATE_ОТКАЗ) || ($model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА && $model->state_id != EdfStates::STATE_ОТКАЗ && (Yii::$app->user->can('operator_head') && $model->state_id >= EdfStates::STATE_ОТПРАВЛЕН))]) ?>

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <?= $form->field($model, 'req_dir_post')->textInput(['maxlength' => true, 'placeholder' => 'Введите должность директора', 'disabled' => ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК && $model->state_id != EdfStates::STATE_ОТКАЗ) || ($model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА && $model->state_id != EdfStates::STATE_ОТКАЗ && (Yii::$app->user->can('operator_head') && $model->state_id >= EdfStates::STATE_ОТПРАВЛЕН))]) ?>

                                    </div>
                                    <div class="col-md-2">
                                        <?= $form->field($model, 'req_dir_name')->textInput([
                                            'maxlength' => true,
                                            'placeholder' => 'Введите ФИО директора полностью',
                                            'title' => 'ФИО директора полностью (в именительном падеже)',
                                            'disabled' => ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК && $model->state_id != EdfStates::STATE_ОТКАЗ) || ($model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА && $model->state_id != EdfStates::STATE_ОТКАЗ && (Yii::$app->user->can('operator_head') && $model->state_id >= EdfStates::STATE_ОТПРАВЛЕН)),
                                        ])->label('Директор полностью') ?>

                                    </div>
                                    <div class="col-md-2">
                                        <?= $form->field($model, 'req_dir_name_of')->textInput([
                                            'maxlength' => true,
                                            'placeholder' => 'Введите должность директора',
                                            'title' => 'ФИО директора полностью (в родительном падеже)',
                                            'disabled' => ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК && $model->state_id != EdfStates::STATE_ОТКАЗ) || ($model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА && $model->state_id != EdfStates::STATE_ОТКАЗ && (Yii::$app->user->can('operator_head') && $model->state_id >= EdfStates::STATE_ОТПРАВЛЕН)),
                                        ])->label('Директор (кого? чего?)') ?>

                                    </div>
                                    <div class="col-md-2">
                                        <?= $form->field($model, 'req_dir_name_short')->textInput([
                                            'maxlength' => true,
                                            'placeholder' => 'Введите должность директора',
                                            'title' => 'ФИО директора сокращенно (в именительном падеже)',
                                            'disabled' => ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК && $model->state_id != EdfStates::STATE_ОТКАЗ) || ($model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА && $model->state_id != EdfStates::STATE_ОТКАЗ && (Yii::$app->user->can('operator_head') && $model->state_id >= EdfStates::STATE_ОТПРАВЛЕН)),
                                        ])->label('Директор сокращенно') ?>

                                    </div>
                                    <div class="col-md-2">
                                        <?= $form->field($model, 'basis')->textInput(['maxlength' => true, 'placeholder' => 'В родительном падеже', 'disabled' => ($hasAccess && !$model->isNewRecord && $model->state_id != EdfStates::STATE_ЧЕРНОВИК && $model->state_id != EdfStates::STATE_ОТКАЗ) || ($model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА && $model->state_id != EdfStates::STATE_ОТКАЗ && (Yii::$app->user->can('operator_head') && $model->state_id >= EdfStates::STATE_ОТПРАВЛЕН))]) ?>

                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <?= $form->field($model, 'req_bik')->widget(\yii\widgets\MaskedInput::className(), [
                                        'mask' => '999999999',
                                        'clientOptions' => ['placeholder' => ''],
                                    ])->textInput(['placeholder' => 'Введите БИК банка', 'disabled' => $hasAccess || $model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА])
                                        ->label($label_bank_bik, ['id' => 'label-bank_bik']) ?>

                                </div>
                                <div class="col-md-3">
                                    <?= $form->field($model, 'req_an')->widget(\yii\widgets\MaskedInput::className(), [
                                        'mask' => '99999999999999999999',
                                        'clientOptions' => ['placeholder' => ''],
                                    ])->textInput(['placeholder' => 'Введите номер расчетного счета', 'disabled' => $hasAccess || $model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА]) ?>

                                </div>
                                <div class="col-md-3">
                                    <?= $form->field($model, 'req_ca')->widget(\yii\widgets\MaskedInput::className(), [
                                        'mask' => '99999999999999999999',
                                        'clientOptions' => ['placeholder' => ''],
                                    ])->textInput(['placeholder' => 'Введите номер корр. счета', 'disabled' => $hasAccess || $model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА]) ?>

                                </div>
                                <div class="col-md-4">
                                    <?= $form->field($model, 'req_bn')->textInput(['maxlength' => true, 'placeholder' => 'Введите наименование банка', 'disabled' => $hasAccess || $model->state_id >= EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА]) ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (!$model->isNewRecord): ?>
                    <div class="form-group">
                    <?= Html::a(Html::img(Url::to(['/images/freshoffice16.png'])) . ' Отправить во Fresh', '#', ['id' => 'btnPushReqsToFresh', 'class' => 'btn btn-warning', 'title' => 'Отправить реквизиты в карточку контрагента в CRM Fresh Office']) ?>

                    </div>
                    <?php endif; ?>
                </div><!-- req -->
                <div role="tabpanel" class="tab-pane fade" id="tp">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            Отходы <span id="waste-preloader" class="collapse"><i class="fa fa-cog fa-spin text-muted"></i></span>
                            <?php if (($hasAccess && $model->isNewRecord) || (!$hasAccess && $model->state_id < EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА) || ($hasAccess && $model->state_id == EdfStates::STATE_СОГЛАСОВАНИЕ) || (Yii::$app->user->can('root') || (Yii::$app->user->can('operator_head')))): ?>
                            <?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i> Добавить', '#', ['id' => 'btnAddFkkoRow', 'class' => 'btn btn-success btn-xs pull-right', 'data-count' => $count, 'title' => 'Добавить строку']) ?>

                            <?= Html::a('Заполнить на основании', '#', ['id' => 'btnFillFkko', 'class' => 'btn btn-success btn-xs pull-right', 'style' => 'margin-right:5px;', 'title' => 'Заполнить табличную часть на основании запроса на транспорт по выбранному контрагенту']) ?>

                            <?= Html::a('Отметить все', '#', ['id' => 'btnCheckAllFkko', 'class' => 'btn btn-default btn-xs pull-right', 'style' => 'margin-right:5px;', 'title' => 'Отметить галочками все строки табличной части']) ?>

                            <?php endif; ?>
                        </div>
                        <div class="panel-body">
                            <?= $form->field($model, 'tpErrors', ['template' => "{error}"])->staticControl() ?>

                            <div id="block-tpWaste">
                                <?php
                                if (count($tp) == 0) echo $add_row_prompt;

                                foreach ($tp as $index => $row)
                                    echo $this->render('_row_fkko', [
                                        'edf' => $model,
                                        'model' => $row,
                                        'counter' => $index,
                                        'count' => $count
                                    ]);
                                ?>

                            </div>
                            <?php if (!$model->isNewRecord): ?>
                            <div class="form-group">
                                <?= Html::a(Html::img(Url::to(['/images/freshoffice16.png'])) . ' Отправить товары во Fresh', '#', ['id' => 'btnPullGoodsToFresh', 'class' => 'btn btn-warning', 'title' => 'Отправить товары в карточку контрагента в CRM Fresh Office']) ?>

                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div><!-- tp -->
            </div>
        </div>
    </div>
    <?php if ($model->state_id != EdfStates::STATE_ЗАВЕРШЕНО): ?>
    <?= $form->field($model, 'reject_reason')->textarea(['rows' => 3, 'placeholder' => 'Введите причину отказа']) ?>

    <?php endif; ?>
    <?php if ($model->isNewRecord): ?>
    <div class="form-group">
        <p>Соберите все необходимые файлы в одном месте, нажмите на кнопку и единоразово отметьте все файлы. Вы можете прикрепить до <strong>10</strong> файлов.</p>
        <?= $form->field($model, 'initialFiles[]')->fileInput(['multiple' => true]) ?>

    </div>
    <?php endif; ?>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . EdfController::ROOT_LABEL, EdfController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?= Html::a('<i class="fa fa-flag-checkered"></i> Завершить', '#', [
            'id' => 'btnFinishEdfForm',
            'class' => 'btn btn-success btn-lg' . (!$model->isNewRecord && $model->state_id != EdfStates::STATE_ЗАВЕРШЕНО && $model->is_received_original ? '' : ' hidden'),
            'title' => 'Документ подписан с обеих сторон, оригинал получен и сдан в бухгалтерию',
        ]) ?>

        <?= $model->renderSubmitButtons() ?>

    </div>
    <?php ActiveForm::end(); ?>

</div>
<div id="modalWindow" class="modal fade" tabindex="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header"><h4 id="modalTitle" class="modal-title">Modal title</h4></div>
            <div id="modalBody" class="modal-body"></div>
            <div class="modal-footer">
                <?= Html::button('Сгенерировать', ['class' => 'btn btn-success collapse', 'id' => 'btnGenerateDocs']) ?>

                <?= Html::button('Завершить документооборот', ['class' => 'btn btn-success collapse', 'id' => 'btnFinishEdf']) ?>

                <?= Html::button('Закрыть', ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>

            </div>
        </div>
    </div>
</div>
<?php
$token = \common\models\DadataAPI::API_TOKEN;
if ($model->isNewRecord) $functionName = 'innOnCreateOnChange'; else $functionName = 'innOnChange';
$edfTpFormName = strtolower((new \common\models\EdfTp())->formName());
$urlRenderFields = Url::to(['/' . EdfController::ROOT_URL_FOR_SORT_PAGING . '/render-fields']);
$urlRenderTemplatesForm = Url::to(['/' . EdfController::ROOT_URL_FOR_SORT_PAGING . '/generate-from-template']);
$urlFillByInnOgrn = Url::to(['/services/fetch-counteragents-info-dadata']);
$urlFillBank = Url::to(['/services/fetch-bank-by-bik']);
$urlFillFkko = Url::to(EdfController::FILL_FKKO_TR_BASIS_AS_ARRAY);
$urlAddFkko = Url::to(['/' . EdfController::ROOT_URL_FOR_SORT_PAGING . '/render-fkko-row']);
$urlDelFkko = Url::to(['/' . EdfController::ROOT_URL_FOR_SORT_PAGING . '/delete-fkko-row']);
$urlFinishEdf = Url::to(EdfController::URL_FINISH_EDF_AS_ARRAY);
$urlPushToFresh = Url::to(EdfController::URL_PUSH_TO_FRESH);
$urlPullGoods = Url::to(EdfController::URL_PULL_GOODS_TO_FRESH);
$urlFkkoOnChange = Url::to(EdfController::FKKO_ONCHANGE_URL_AS_ARRAY);
$urlParentOnChange = Url::to(EdfController::PARENT_ONCHANGE_URL_AS_ARRAY);

$this->registerJs(<<<JS
// Заполняет реквизиты данными, полученными через механизм API.
//
function fillFields(caInfo) {
    \$field = $("#$formName-req_inn");
    \$field.val("");
    if (caInfo.inn) \$field.val(caInfo.inn);

    \$field = $("#$formName-req_kpp");
    \$field.val("");
    if (caInfo.kpp) \$field.val(caInfo.kpp);

    \$field = $("#$formName-req_ogrn");
    \$field.val("");
    if (caInfo.ogrn) \$field.val(caInfo.ogrn);

    \$field = $("#$formName-req_name_full");
    \$field.val("");
    if (caInfo.name_full) \$field.val(caInfo.name_full);

    \$field = $("#$formName-req_name_short");
    \$field.val("");
    if (caInfo.name_short) \$field.val(caInfo.name_short);

    \$field = $("#$formName-req_address_j");
    \$field.val("");
    if (caInfo.address) \$field.val(caInfo.address);

    \$field = $("#$formName-req_dir_name");
    \$field.val("");
    if (caInfo.dir_name) \$field.val(caInfo.dir_name);

    \$field = $("#$formName-req_dir_name_of");
    \$field.val("");
    if (caInfo.dir_name_of) \$field.val(caInfo.dir_name_of);

    \$field = $("#$formName-req_dir_name_short");
    \$field.val("");
    if (caInfo.dir_name_short) \$field.val(caInfo.dir_name_short);

    \$field = $("#$formName-req_dir_name_short_of");
    \$field.val("");
    if (caInfo.dir_name_short_of) \$field.val(caInfo.dir_name_short_of);

    \$field = $("#$formName-req_dir_post");
    \$field.val("");
    if (caInfo.dir_post) \$field.val(caInfo.dir_post);
} // fillFields()

function fkkoOnChange(fkko_id, counter) {
    $.get("$urlFkkoOnChange?fkko_id=" + fkko_id + "&counter=" + counter, function(retval) {
        $("#$edfTpFormName-unit_id-" + counter).val(retval.unit_id).trigger("change");
        $("#$edfTpFormName-hk_id-" + counter).val(retval.hk_id).trigger("change");
    });
} // fkkoOnChange()

// Обработчик изменения значения в поле "Тип документа".
//
function typeOnChange() {
    type_id = $("#$formName-type_id").val();
    if ((type_id != "") && (type_id != undefined)) {
        url = "$urlRenderFields?kind=1&value=" + type_id;
        ca_id = $("#$formName-fo_ca_id").val();
        if ((ca_id != "") && (ca_id != undefined)) url += "&ca_id=" + ca_id;
        \$block = $("#block-type");
        \$block.html('<p class="text-center"><i class="fa fa-spinner fa-pulse fa-fw text-primary text-muted"></i><span class="sr-only">Подождите...</span></p>');
        \$block.load(url);
    }
} // typeOnChange()

// Обработчик изменения значения в поле "Родительский документ".
//
function parentOnChange() {
    ca_id = $("#$formName-fo_ca_id").val();
    parent_id = $("#$formName-parent_id").val();
    $.get("$urlParentOnChange?parent_id=" + parent_id, function(retval) {
        // организация
        $("#$formName-org_id").val(retval.org_id).trigger("change");
        // контрагент
        var newOption = new Option(retval.caName, retval.ca_id, true, true);
        $("#$formName-fo_ca_id").append(newOption);
        // ИНН и КПП
        $("#$formName-req_inn").val(retval.inn);
        $("#$formName-req_kpp").val(retval.kpp);
        $functionName();
        // банковские реквизиты
        $("#$formName-req_bn").val(retval.bank_bn);
        $("#$formName-req_an").val(retval.bank_an);
        $("#$formName-req_bik").val(retval.bank_bik);
        $("#$formName-req_ca").val(retval.bank_ca);
    });
} // parentOnChange()

// Обработчик изменения значения в поле "ИНН" при создании электронного документа.
//
function innOnCreateOnChange() {
    \$block = $("#block-reqs");
    inn = $("#$formName-req_inn").val();
    kpp = $("#$formName-req_kpp").val();
    if (kpp != "" && kpp != undefined) kpp = "&specifyingValue=" + kpp;
    if (inn != "") {
        \$label = $("#label-inn");
        \$label.html("$label_inn &nbsp;<i class=\"fa fa-spinner fa-pulse fa-fw text-primary\"></i>");
        $.get("$urlFillByInnOgrn?query=" + inn + kpp + "&cleanDir=1", function(response) {
            if (response != false) {
                // заполняем скрытые поля
                fillFields(response);

                result = "";

                if (response.name_short) result += "<strong>" + response.name_short + "</strong>";
                if (response.inn) result += ", <strong>ИНН</strong> " + response.inn;
                if (response.kpp) result += ", <strong>КПП</strong> " + response.kpp;
                if (response.ogrn) result += ", <strong>ОГРН</strong> " + response.ogrn;
                if (response.address) result += ", <strong>юр. адрес</strong> " + response.address;

                result = "<p>" + result + "</p>";

                director = "";
                if (response.dir_name) director = response.dir_name;
                if (response.dir_post) director = response.dir_post + " " + director;
                if (director != "") result += "<p>" + director + "</p>";

                \$block.html(result);
            }
        }).always(function() {
            \$label.html("$label_inn");
        });
    }
    else \$block.html('$promptEmptyInn');
} // innOnCreateOnChange()

// Обработчик изменения значения в поле "ИНН".
//
function innOnChange() {
    ogrn = $("#$formName-req_ogrn").val();
    kpp = $("#$formName-req_kpp").val();
    if (ogrn == "" || kpp == "") {
        inn = $("#$formName-req_inn").val();
        if (inn != "") {
            \$label = $("#label-inn");
            \$label.html("$label_inn &nbsp;<i class=\"fa fa-spinner fa-pulse fa-fw text-primary\"></i>");
            $.get("$urlFillByInnOgrn?query=" + inn + "&cleanDir=1", function(response) {
                if (response != false) {
                    fillFields(response);
                }
   
            }).always(function() {
                \$label.html("$label_inn");
            });
        }
    }
} // innOnChange()
JS
, yii\web\View::POS_BEGIN);

$this->registerCssFile('https://cdn.jsdelivr.net/npm/suggestions-jquery@19.4.2/dist/css/suggestions.min.css');

$this->registerJsFile('https://cdn.jsdelivr.net/npm/suggestions-jquery@19.4.2/dist/js/jquery.suggestions.min.js', ['depends' => 'yii\web\JqueryAsset', 'position' => View::POS_END]);

$this->registerJs(<<<JS
$("input[type='checkbox']").iCheck({checkboxClass: "icheckbox_square-green"});

$("#dadataCasting").suggestions({
    token: "$token",
    type: "PARTY",
    onSelect: function(suggestion) {
        $("#$formName-req_inn").val("");
        $("#$formName-req_kpp").val("");
        $("#$formName-req_ogrn").val("");
        $("#$formName-req_name").val("");
        $("#$formName-req_name_full").val("");
        $("#$formName-req_name_short").val("");
        $("#$formName-req_address_j").val("");
        $("#$formName-req_dir_name").val("");
        $("#$formName-req_dir_post").val("");

        if (suggestion.data.state.liquidation_date != null)  {
            if (!confirm("Предприятие ликвидировано! Вы действительно хотите продолжить?")) {
                return true;
            }
        }

        response = suggestion.data;
        \$block = $("#block-reqs");
        result = "";

        if (response.name) result += "<strong>" + response.name.short_with_opf + "</strong>";
        if (response.inn) result += ", <strong>ИНН</strong> " + response.inn;
        if (response.kpp) result += ", <strong>КПП</strong> " + response.kpp;
        if (response.ogrn) result += ", <strong>ОГРН</strong> " + response.ogrn;
        if (response.address) result += ", <strong>юр. адрес</strong> " + response.address.value;

        result = "<p>" + result + "</p>";

        director = "";
        if (response.dir_name) director = response.dir_name;
        if (response.dir_post) director = response.dir_post + " " + director;
        if (director != "") result += "<p>" + director + "</p>";

        \$block.html(result);

        if (suggestion.data.inn) $("#$formName-req_inn").val(suggestion.data.inn);
        if (suggestion.data.kpp) $("#$formName-req_kpp").val(suggestion.data.kpp);
        if (suggestion.data.ogrn) $("#$formName-req_ogrn").val(suggestion.data.ogrn);
        if (suggestion.data.name.full) $("#$formName-req_name").val(suggestion.data.name.full);
        if (suggestion.data.name.full_with_opf) $("#$formName-req_name_full").val(suggestion.data.name.full_with_opf);
        if (suggestion.data.name.short_with_opf) $("#$formName-req_name_short").val(suggestion.data.name.short_with_opf);
        if (suggestion.data.address.value) $("#$formName-req_address_j").val(suggestion.data.address.value);
        if (suggestion.data.management && suggestion.data.management.name) $("#$formName-req_dir_name").val(suggestion.data.management.name);
        if (suggestion.data.management && suggestion.data.management.post) $("#$formName-req_dir_post").val(suggestion.data.management.post);

        return true;
    }
});

// Обработчик установки/снятия галочки "Оригинал получен".
//
function originalOnChanged() {
    $("#btnFinishEdfForm").toggleClass("hidden");
} // originalOnChanged()

// Обработчик изменения значения в поле "Организация".
//
function orgOnChange() {
    url = "$urlRenderFields?kind=2&value=" + $(this).val();
    type_id = $("#$formName-type_id").val();
    if ((type_id != "" && type_id != undefined)) url += "&type_id=" + type_id;
    \$block = $("#block-ba");
    \$block.html('<p class="text-center"><i class="fa fa-spinner fa-pulse fa-fw text-primary text-muted"></i><span class="sr-only">Подождите...</span></p>');
    \$block.load(url);
} // orgOnChange()

// Обработчик изменения значения в поле "БИК банка".
//
function bankBikOnChange() {
    bik = $(this).val();
    if (bik.length == 9) {
        $("#$formName-req_ca").val("");
        $("#$formName-req_bd").val("");
        $("#label-bank_bik").html("$label_bank_bik &nbsp;<i class=\"fa fa-spinner fa-pulse fa-fw text-primary\"></i>");
        $.get("$urlFillBank?bik=" + bik, function(response) {
            if (response != false) {
                $("#$formName-req_ca").val(response.bank_ca);
                $("#$formName-req_bn").val(response.bank_name);
            };

        }).always(function() {
            $("#label-bank_bik").html("$label_bank_bik");
        });
    }
} // bankBikOnChange()

// Обработчик щелчка по кнопке "Заполнить на основании" в табличной части "Отходы".
//
function btnFillFkkoOnClick() {
    ca_id = $("#$formName-fo_ca_id").val();
    if (ca_id != "" && ca_id != undefined) {
        $("#btnGenerateDocs").hide();
        $("#btnFinishEdf").hide();
        $("#modalTitle").text("Выбор документа-основания");
        \$body = $("#modalBody");
        \$body.html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#modalWindow").modal();
        \$body.load("$urlFillFkko?ca_id=" + ca_id);
    }

    return false;
} // btnFillFkkoOnClick()

// Обработчик щелчка по кнопке "Добавить" в форме заполнения табличной части на основании запроса на транспорт.
//
function btnSubmitFillFkkoFormOnClick(e) {
    e.preventDefault();

    counter = parseInt($("#btnAddFkkoRow").attr("data-count"));
    $("#waste-preloader").show();
    $.post("$urlFillFkko?counter=" + counter, $("#frmFillFkko").serialize(), function (data) {
        if ($("div[id ^= 'fkko-row-']").length == 0) $("#block-tpWaste").html("");
        $("#block-tpWaste").append(data);
        $("#waste-preloader").hide();
    });
    $("#modalWindow").modal("hide");

    return false;
} // btnSubmitFillFkkoFormOnClick()

// Обработчик щелчка по кнопке "Добавить строку" в табличной части "Отходы".
//
function btnAddFkkoRowOnClick() {
    counter = parseInt($(this).attr("data-count"));
    next_counter = counter+1;
    $("#waste-preloader").show();
    $.get("$urlAddFkko?counter=" + counter, function(data) {
        if ($("div[id ^= 'fkko-row-']").length == 0) $("#block-tpWaste").html("");
        $("#block-tpWaste").append(data);
        $("#waste-preloader").hide();
    });

    // наращиваем количество добавленных строк
    $(this).attr("data-count", next_counter);

    return false;
} // btnAddFkkoRowOnClick()

// Обработчик изменения значения в поле "Количество".
//
function calcRowAmount() {
    row_num = $(this).attr("data-num");
    measure = parseFloat($("#edftp-measure-" + row_num).val().replace(" ", ""));
    price = parseFloat($("#edftp-price-" + row_num).val().replace(" ", ""));
    if ((measure != "") && (measure != undefined) && (row_num != "") && (row_num != undefined)) {
        $("#edftp-amount-" + row_num).val(measure * price);
    }
} // calcRowAmount()

// Обработчик щелчка по кнопке "Удалить строку" в табличной части "Отходы".
//
function btnDeleteFkkoRowClick(event) {
    var message = "Удаление строки из табличной части производится сразу и безвозвратно. Продолжить?";
    var id = $(this).attr("data-id");
    var counter = $(this).attr("data-counter");

    // может быть, это просто новая строка
    // тогда просто ее удаляем и никаких post-запросов
    if (id == undefined) {
        $("#fkko-row-" + counter).remove();
        if ($("div[id ^= 'fkko-row-']").length == 0) $("#block-tpWaste").html('$add_row_prompt');
        return false;
    }

    if (confirm(message))
        $.ajax({
            type: "POST",
            url: "$urlDelFkko" + "?id=" + id,
            dataType: "json",
            async: false,
            success: function(result) {
                if (result == true) {
                    $("#fkko-row-" + counter).remove();
                    if ($("div[id ^= 'fkko-row-']").length == 0) $("#block-tpWaste").html('$add_row_prompt');
                }
            }
        });

    return false;
} // btnDeleteFkkoRowClick()

// Обработчик нажатия на кнопку "Сгенерировать".
// Открывает форму, из которой можно сгенерировать назначенные для выбранного типа документы.
//
function btnGenerateFromTmplsFormOnClick() {
    $("#btnGenerateDocs").show();
    $("#btnFinishEdf").hide();
    dt_id = $("#$formName-type_id").val();
    ct_id = $("#$formName-ct_id").val();
    if (dt_id != "") {
        $("#modalTitle").text("Генерация документов из шаблонов");
        \$body = $("#modalBody");
        \$body.html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
        $("#modalWindow").modal();
        \$body.load("$urlRenderTemplatesForm?dt_id=" + dt_id + "&ct_id=" + ct_id);
    }

    return false;
} // btnGenerateFromTmplsFormOnClick()

// Обработчик нажатия на кнопку "Сгенерировать" в форме генерации документов из шаблонов.
//
function btnGenerateDocsOnClick() {
    var keys = $("#gwTemplates").yiiGridView('getSelectedRows');
    $.post("$urlRenderTemplatesForm", {id: $modelId, tmpl_ids: keys})
    .done(function() {
        $.pjax.reload({container:"#pjax-files"});
        $("#modalWindow").modal("hide");
    });
} // btnGenerateDocsOnClick()

// Обработчик нажатия на кнопку "Завершить документооборот".
//
function btnRenderFinishEdfFormOnClick() {
    $("#btnGenerateDocs").hide();
    $("#btnFinishEdf").show();
    $("#modalTitle").text("Отметьте файлы для помещения в хранилище");
    \$body = $("#modalBody");
    \$body.html('<p class="text-center"><i class="fa fa-cog fa-spin fa-3x text-info"></i><span class="sr-only">Подождите...</span></p>');
    $("#modalWindow").modal();
    \$body.load("$urlFinishEdf?id=$modelId");

    return false;
} // btnRenderFinishEdfFormOnClick()

// Обработчик нажатия на кнопку "Сгенерировать" в форме генерации документов из шаблонов.
//
function btnFinishEdfOnClick() {
    $.post("$urlFinishEdf", $("#frmFinishEdf").serialize())
    .done(function() {
        $("#modalWindow").modal("hide");
        window.location = "/edf";
    });
} // btnFinishEdfOnClick()

// Обработчик щелчка по кнопке "Отправить реквизиты в карточку контрагента Fresh Office".
//
function btnPushReqsToFreshOnClick() {
    if (confirm("Если данные реквизиты уже передавались или имеются в карточке контрагента, то ничего не произойдет. Если данные реквизиты обнаружены не будут, то в карточке контрагента появится новая запись на вкладке Реквизиты. Продолжить?")) {
        $.post("$urlPushToFresh", {id: $modelId})
        .done(function(retval) {
            if (retval == true) {
                alert("Реквизиты успешно сохранены!");
            }
            else {
                alert("Не удалось передать реквизиты во Fresh Office!");
            }
        });
    }

    return false;
} // btnPushReqsToFreshOnClick()

// Обработчик щелчка по кнопке "Отправить товары в карточку контрагента Fresh Office".
//
function btnPullGoodsToFreshOnClick() {
    if (confirm("Документ должен быть сохранен, галочки должны быть отмечены для тех товаров, наименования которых необходимо дополнить. Вы действительно хотите отправить товары в карточку контрагента?")) {
        var favorite = [];
        $.each($("input[id ^= '$edfTpFormName-is_addon_required']:checked"), function(){
            favorite.push($(this).val());
        });

        $.post("$urlPullGoods", {id: $modelId, addons: favorite})
        .done(function(retval) {
            alert("Передача товаров во Fresh Office завершена.");
        });
    }

    return false;
} // btnPullGoodsToFreshOnClick()

// Обработчик щелчка по кнопке "Отметить все строки табличной части".
//
function btnCheckAllFkkoOnClick() {
    $.each($("input[id ^= '$edfTpFormName-is_addon_required']"), function(){
        $(this).iCheck("check");
    });

    return false;
} // btnCheckAllFkkoOnClick()

$("#$formName-is_received_original").on("ifChanged", originalOnChanged);
$(document).on("change", "#$formName-req_bik", bankBikOnChange);
$(document).on("change", "#$formName-org_id", orgOnChange);
$(document).on("change", "#$formName-type_id", typeOnChange);

$(document).on("click", "#btnFillFkko", btnFillFkkoOnClick);
$(document).on("click", "#btnSubmitFillFkkoForm", btnSubmitFillFkkoFormOnClick);
$(document).on("click", "#btnAddFkkoRow", btnAddFkkoRowOnClick);
$(document).on("change", "input[id ^= 'edftp-measure'],input[id ^= 'edftp-price']", calcRowAmount);
$(document).on("click", "a[id ^= 'btnDeleteFkkoRow']", btnDeleteFkkoRowClick);
$(document).on("click", "#btnGenerateFromTmplsForm", btnGenerateFromTmplsFormOnClick);
$(document).on("click", "#btnGenerateDocs", btnGenerateDocsOnClick);
$(document).on("click", "#btnFinishEdfForm", btnRenderFinishEdfFormOnClick);
$(document).on("click", "#btnFinishEdf", btnFinishEdfOnClick);
$(document).on("click", "#btnPushReqsToFresh", btnPushReqsToFreshOnClick);
$(document).on("click", "#btnPullGoodsToFresh", btnPullGoodsToFreshOnClick);
$(document).on("click", "#btnCheckAllFkko", btnCheckAllFkkoOnClick);

$("#$formName-type_id").change();
JS
, View::POS_READY);
