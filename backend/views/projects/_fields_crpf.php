<?php

use kartik\select2\Select2;
use kartik\typeahead\Typeahead;

/* @var $this yii\web\View */
/* @var $model common\models\CustomerRatingProposalForm */
/* @var $form yii\bootstrap\ActiveForm */

$contactEmails = $model->fetchCompanyEmails();
?>
<div class="col-md-3">
    <?= $form->field($model, 'ca_name')->textInput(['disabled' => true, 'title' => $model->ca_name]) ?>

</div>
<div class="col-md-3">
    <?= $form->field($model, 'cp_id')->widget(Select2::className(), [
        'data' => $model->arrayMapOfContactPersons(),
        'theme' => Select2::THEME_BOOTSTRAP,
        'options' => ['placeholder' => '- выберите -'],
    ]) ?>

</div>
<div class="col-md-2">
    <?= $form->field($model, 'email')->widget(Typeahead::class, [
        'options' => [
            'placeholder' => 'Введите E-mail',
            'title' => 'На этот E-mail заказчика будет отправлено предложение оценить работу по проекту',
            'autocomplete' => 'off',
        ],
        'pluginOptions' => ['highlight' => true],
        'defaultSuggestions' => (count($contactEmails) == 1 && empty($contactEmails[0])) ? [] : $contactEmails,
        'dataset' => [
            [
                'local' => $contactEmails,
                'limit' => 20,
            ]
        ],
    ])->label('E-mail') ?>

</div>
<?= $form->field($model, 'ca_id')->hiddenInput()->label(false) ?>

<?= $form->field($model, 'ca_name')->hiddenInput()->label(false) ?>
