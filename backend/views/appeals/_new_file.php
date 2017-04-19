<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Appeals */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="col-md-6">
    <?= $form->field($model, 'files[]')->fileInput(['multiple' => true]) ?>

</div>
<div class="col-md-1">
    <label class="control-label" for="<?= 'btn-delete-row-'.$counter ?>">&nbsp;</label>
    <div class="form-group">
        <?= Html::a('<i class="fa fa-minus" aria-hidden="true"></i></span>', '#', ['class' => 'btn btn-danger btn-xs']) ?>

    </div>
</div>