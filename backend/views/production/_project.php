<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $project array массив с данными проекта */
/* @var $model \common\models\ProductionFeedbackForm */
/* @var $form yii\bootstrap\ActiveForm */

$mismatch_tp = '';
$formName = $model->formName();
$formNameForId = strtolower($model->formName());
?>

<div id="block-project_details" class="panel panel-default collapse in">
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-condensed">
                <thead>
                <tr>
                    <th>Контрагент</th>
                    <th>Тип проекта</th>
                    <th>Статус проекта</th>
                    <th>Контактное лицо</th>
                    <th>Дата вывоза</th>
                    <th>Ответственный</th>
                    <th>Перевозчик</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?= $project['ca_name'] ?></td>
                    <td><?= $project['type_name'] ?></td>
                    <td><?= $project['state_name'] ?></td>
                    <td><?= $project['contact_name'] ?> <?= $project['contact_phone'] ?></td>
                    <td><?= Yii::$app->formatter->asDate($project['vivozdate'], 'php:d.m.Y') ?></td>
                    <td><?= $project['manager_name'] ?></td>
                    <td><?= $project['ferryman'] ?></td>
                </tr>
                </tbody>
            </table>
        </div>
        <?php if ($project['comment'] != null): ?>
        <div class="well well-small">
            <?= nl2br($project['comment']) ?>

        </div>
        <?php endif; ?>

        <?php if ($project['properties'] != null): ?>
        <div class="row">
            <div class="col-md-7 col-lg-6">
                <h4 class="text-center">Параметры проекта</h4>
                <table class="table table-bordered">
                <?php
                    foreach($project['properties'] as $property) {
                    if (strpos($property['property'], 'Оплата ТС') !== false) continue;
                ?>
                    <tr>
                        <td class="active"><strong><?= $property['property'] ?></strong></td>
                        <td><?= $property['value'] ?></td>
                    </tr>
                <?php } ?>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($project['tp'] != null): ?>
        <div class="form-group">
            <h4 class="text-center">Товары и услуги</h4>
            <table class="table table-bordered">
            <?php
            $iterator = 0;
            foreach ($project['tp'] as $property) {
                $mismatch_tp .= '
                <tr>
                    <td><strong>' . $property['property'] . '</strong></td>
                    <td>' . $property['value'] . ' ' . $property['ED_IZM_TOVAR'] . '</td>
                    <td>' .
                        Html::hiddenInput('ProductionFeedbackForm[tp][' . $iterator . '][name]', $property['property']) .
                        Html::hiddenInput('ProductionFeedbackForm[tp][' . $iterator . '][value]', $property['value'] . ' ' . $property['ED_IZM_TOVAR']) .
                        Html::input('text', 'ProductionFeedbackForm[tp][' . $iterator . '][fact]', null, [
                            'class' => 'form-control input-sm',
                            'placeholder' => 'Введите факт',
                            'title' => 'Введите фактическое значение',
                        ]) .
                    '</td>
                </tr>
';
                $iterator++;
                ?>
                <tr>
                    <td><strong><?= $property['property'] ?></strong></td>
                    <td><?= $property['value'] . ' ' . $property['ED_IZM_TOVAR'] ?></td>
                </tr>
            <?php }; ?>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $form = ActiveForm::begin([
    'id' => 'frmFeedback',
    'action' => '/production/process-project',
]); ?>

<?= $form->field($model, 'action')->hiddenInput()->label(false) ?>

<?= $form->field($model, 'project_id')->hiddenInput()->label(false) ?>

<?= $form->field($model, 'ca_id')->hiddenInput()->label(false) ?>

<?= $form->field($model, 'ca_name')->hiddenInput()->label(false) ?>

<?= $form->field($model, 'message_subject')->hiddenInput()->label(false) ?>

<div id="block-feedback" class="collapse">
    <?php if ($project['tp'] != null): ?>
    <div id="block-invoice_mismatch" class="collapse">
        <p>Заполнять таблицу нужно <strong>только</strong> там, где не совпадает!</p>
        <table class="table table-bordered table-condensed">
            <?= $mismatch_tp; ?>

        </table>
    </div>
    <?php endif; ?>
    <div class="form-group">
        <?= $form->field($model, 'message_subject')->staticControl() ?>

    </div>
    <div class="form-group">
        <?= $form->field($model, 'message_body')->textarea([
            'rows' => '4',
            'value' => 'Проект № ' . $project['id'] . ', контрагент ' . $project['ca_name'] . '.',
            'placeholder' => 'Введите текст письма',
        ]) ?>

    </div>
    <div class="form-group">
        <p>Соберите все необходимые файлы в одном месте, нажмите на кнопку и единоразово отметьте все файлы. Вы можете прикрепить до <strong>100</strong> файлов.</p>
        <?= $form->field($model, 'files[]')->fileInput(['multiple' => true]) ?>

    </div>
    <p>
        <?= Html::submitButton('<i class="fa fa-plane" aria-hidden="true"></i> Отправить', [
            'id' => 'sendFeedback',
            'class' => 'btn btn-default btn-lg',
            'title' => 'Вернуться в список. Изменения не будут сохранены'
        ]) ?>

    </p>
</div>
<?php ActiveForm::end(); ?>

<div id="block-documents_match">
    <label class="control-label" for="<?= $formNameForId ?>-documents_match">Груз соответствует документам?</label>
    <div>
        <?= Html::radioList((new ReflectionClass($model))->getShortName() . '[documents_match]', null, ArrayHelper::map([
            [
                'id' => 0,
                'name' => '<i class="fa fa-thumbs-down" aria-hidden="true"></i> Груз документам не соответствует',
            ],
            [
                'id' => 1,
                'name' => '<i class="fa fa-thumbs-up" aria-hidden="true"></i> Груз соответствует документам',
            ],
        ], 'id', 'name'), [
            'id' => $formNameForId . '-documents_match',
            'class' => 'btn-group',
            'data-toggle' => 'buttons',
            'unselect' => null,
            'item' => function ($index, $label, $name, $checked, $value) {
                switch ($value) {
                    case 0:
                        return '<label class="btn btn-danger btn-lg' . ($checked ? ' active' : '') . '">' .
                            Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn', 'id' => 'productionclosingprojects-is_match']) . $label . '</label>';
                        break;
                    case 1:
                        return '<label class="btn btn-success btn-lg' . ($checked ? ' active' : '') . '">' .
                            Html::radio($name, $checked, ['value' => $value, 'class' => 'types-btn', 'id' => 'productionclosingprojects-mismatch']) . $label . '</label>';
                        break;
                }
            },
        ]) ?>

    </div>
</div>
