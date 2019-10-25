<?php

use backend\controllers\PoPropertiesController;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\PoValues;

/* @var $this yii\web\View */
/* @var $model common\models\PoValues */
/* @var $form yii\bootstrap\ActiveForm */
?>

<?php $form = ActiveForm::begin([
    'id' => PoValues::DOM_IDS['PJAX_FORM_ID'],
    'action' => PoPropertiesController::URL_CREATE_VALUE_AS_ARRAY,
    'options' => ['data-pjax' => true],
]); ?>

<div class="panel panel-success">
    <div class="panel-heading">Форма добавления</div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-8">
                <?= $form->field($model, 'name')->textInput([
                    'class' => 'form-control input-sm',
                    'placeholder' => 'Введите заголовок',
                    'title' => 'Введите значение свойства',
                ]) ?>

            </div>
            <div class="col-md-2">
                <label class="control-label">&nbsp;</label>
                <?= Html::submitButton('Добавить <i class="fa fa-arrow-down"></i> ', ['class' => 'btn btn-success btn-xs']) ?>

            </div>
        </div>
        <?= $form->field($model, 'property_id', ['template' => '{input}', 'options' => ['tag' => false]])->hiddenInput()->label(false) ?>

    </div>
</div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs(<<<JS
$("input[type='checkbox']").iCheck({checkboxClass: "icheckbox_square-green"});
JS
, yii\web\View::POS_READY);
?>
