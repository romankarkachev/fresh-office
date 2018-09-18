<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model cemail\models\UserAccessForm */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $mailboxes array массив почтовых ящиков */
/* @var $userAccess array массив доступных пользователю почтовых ящиков */
?>

<p>
    <?= Html::a('Отметить (сбросить) все', '#', ['id' => 'toggleChecked', 'class' => 'link-ajax', 'title' => 'Выделить (снять выделение) все']) ?>

</p>
<div class="row">
<?php
$iterator = 0;
foreach ($mailboxes as $id => $name) {
?>
    <?php if ($iterator == 0): ?>
    <div class="col-md-2">
    <?php else: ?>
    <?php if ($iterator == 15): ?>
    <?php if ($iterator != 0): ?>
    </div>
    <?php endif; ?>
    <div class="col-md-2">
    <?php $iterator = 0; ?>
    <?php endif; ?>
    <?php endif; ?>
        <div class="form-group">
            <?= Html::input('checkbox', $model->formName() . '[mailboxes][]', $id, ['id' => 'mailbox' . $id, 'checked' => in_array($id, $userAccess, false)]) ?>

            <label class="control-label" for="<?= 'mailbox' . $id ?>"><?= $name ?></label>
        </div>
<?php
$iterator++;
};
?>
</div>
<?php
$this->registerJs(<<<JS
$("input").iCheck({checkboxClass: "icheckbox_square-green"});
JS
, yii\web\View::POS_READY);
?>
