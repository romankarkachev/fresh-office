<?php
/* @var $module dektrium\user\Module */
/* @var $force bool выводить принудительно */
?>
<?php if ($module->enableFlashMessages || isset($force)): ?>
<?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
    <?php if (in_array($type, ['success', 'danger', 'warning', 'info'])): ?>
    <?= \yii\bootstrap\Alert::widget([
        'options' => ['class' => 'alert-' . $type],
        'body' => $message
    ]) ?>

    <?php endif ?>
<?php endforeach ?>
<?php endif ?>
