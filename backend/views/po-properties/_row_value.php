<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $property common\models\PoProperties */
/* @var $model common\models\PoValues */
/* @var $form yii\bootstrap\ActiveForm */

$propertyFormName = $property->formName();
$formName = strtolower($model->formName());

// если производится создание объекта, то просто удаление строки
$delete_options = ['id' => 'btnDeleteValueRow-' . $counter, 'class' => 'btn btn-danger btn-xs', 'data-counter' => $counter, 'title' => 'Удалить это значение'];
if (!$model->isNewRecord)
    // если происходит редактирование существующего объекта, то кнопка дополняется подтверждением удаления и идентификатором удаляемой записи
    $delete_options['data-id'] = $model->id;
?>

    <div class="row" id="value-row-<?= $counter ?>">
        <div class="col-md-9">
            <?= $form->field($model, 'name')->textInput([
                'id' => $formName . '-name-' . $counter,
                'name' => $propertyFormName . '[values][' . $counter . '][name]',
                'class' => 'form-control input-sm',
                'placeholder' => 'Введите заголовок',
                'title' => 'Введите значение свойства',
            ])->label(false) ?>

        </div>
        <div class="col-md-1">
            <div class="form-group">
                <?= Html::a('<i class="fa fa-minus" aria-hidden="true"></i>', '#', $delete_options) ?>

            </div>
        </div>
    </div>
<?php
$this->registerJs(<<<JS
$("input[type='checkbox']").iCheck({checkboxClass: 'icheckbox_square-green'});
JS
, \yii\web\View::POS_READY);
