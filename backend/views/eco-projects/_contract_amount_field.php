<?php

use yii\widgets\MaskedInput;

/* @var $this yii\web\View */
/* @var $model common\models\EcoProjects */
/* @var $form yii\bootstrap\ActiveForm */
?>

<?php if (Yii::$app->user->can('root') || Yii::$app->user->can('ecologist_head')): ?>
<div class="col-md-2">
    <?= $form->field($model, 'contract_amount', [
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
    ]) ?>

</div>
<?php endif; ?>
