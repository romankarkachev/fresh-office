<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;
use common\models\TendersResults;
use backend\controllers\TendersController;

/* @var $this yii\web\View */
/* @var $model \common\models\TendersResults */
?>
<?php if (!empty($model->id)): ?>
<p class="lead">
    Победитель: <strong><?= $model->name ?></strong>
    с ценой <strong><?= Yii::$app->formatter->asDecimal($model->price) ?></strong>,
    реестровая запись размещена <?= Yii::$app->formatter->asDate($model->placed_at, 'php:d.m.Y в H:i')  ?>
</p>
<?php else: ?>
<p class="lead">Победитель не определен.</p>
<?php $form = ActiveForm::begin([
    'id' => TendersResults::DOM_IDS['FORM_ID'],
    'action' => Url::to([TendersController::URL_SUBMIT_TENDER_RESULTS]),
]); ?>

<div class="row">
    <div class="col-md-6">
        <?= Html::label('Подбор контрагентов') ?>
        <?= Html::input('text', $model->formName() . '[dadataCasting]', null, [
            'id' => 'dadataCasting',
            'class' => 'form-control',
            'placeholder' => 'Начните вводить наименование или реквизиты',
            'title' => 'Универсальный подбор и автозаполнение реквизитов контрагентов',
            'autofocus' => true,
        ]) ?>

    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'price', [
            'template' => '{label}<div class="input-group">{input}<span class="input-group-addon"><i class="fa fa-rub"></i></span></div>{error}'
        ])->widget(MaskedInput::class, [
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
        ]) ?>

    </div>
</div>
<?= $form->field($model, 'tender_id', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>

<?= $form->field($model, 'name', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>

<?= $form->field($model, 'inn', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>

<?= $form->field($model, 'kpp', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>

<?= $form->field($model, 'ogrn', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput()->label(false) ?>

<?= Html::a('Сохранить', '#', [
    'id' => TendersResults::DOM_IDS['BUTTON_ID'],
    'class' => 'btn btn-default',
    'title' => 'Нажмите, что интерактивно сохранить данные о победителе',
    'data-loading-text' => '<i class="fa fa-cog fa-spin fa-lg text-info"></i> Операция выполняется...',
]) ?>

<?php ActiveForm::end(); ?>

<?php endif; ?>
