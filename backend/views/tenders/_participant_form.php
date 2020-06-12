<?php

use yii\bootstrap\ActiveForm;
use common\models\TenderParticipantForms;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider of common\models\TenderFormsKindsFields[] */

$model = new TenderParticipantForms();
$form = new ActiveForm();
$formName = $model->formName();
$formNameId = strtolower($formName);

$currentFormName = null;
if ($dataProvider->getTotalCount()):
foreach ($dataProvider->models as $field):
if ($currentFormName != $field->kindName):
if (!empty($currentFormName)):
?>
</div>
<?php endif; ?>
<div id="<?= md5($field->id) ?>" class="form-group">
    <h3><?= $field->kindName ?></h3>
<?php endif; ?>
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'items')->textInput([
                'id' => $formNameId . '-' . $field->kind_id . '-' . $field->alias,
                'name' => $formName . '[items][' .$field->kind_id . '][' . $field->alias . ']',
                'placeholder' => !empty($field->description) ? $field->description : $field->widgetPlaceholder,
                'title' => !empty($field->description) ? $field->description : '',
            ])->label($field->name) ?>

        </div>
    </div>
<?php $currentFormName = $field->kindName; ?>
<?php endforeach; ?>
</div>
<?php endif; ?>
