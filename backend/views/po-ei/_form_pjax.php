<?php

use backend\controllers\PoPropertiesController;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\PoEip;

/* @var $this yii\web\View */
/* @var $model common\models\PoEip */
/* @var $form yii\bootstrap\ActiveForm */
?>

<?php $form = ActiveForm::begin([
    'id' => PoEip::DOM_IDS['PJAX_FORM_ID'],
    'action' => PoPropertiesController::URL_LINK_ITEM_AS_ARRAY,
    'options' => ['data-pjax' => true],
]); ?>

<div class="panel panel-success">
    <div class="panel-heading">Форма добавления</div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-8">
                <?= $form->field($model, 'ei_id')->widget(Select2::className(), [
                    'data' => \common\models\PoEi::arrayMapByGroupsForSelect2(),
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'size' => Select2::SMALL,
                    'options' => [
                        'placeholder' => '- выберите статью -',
                        'title' => 'Выберите статью, которая может быть описана данным свойством'
                    ],
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
