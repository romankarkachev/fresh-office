<?php

/* @var $this yii\web\View */
/* @var $user dektrium\user\models\User */
/* @var $model \common\models\UsersTrusted */
/* @var $dataProvider \yii\data\ArrayDataProvider */

$this->params['specifiedTitle'] = 'Доверенные лица';
?>

<?php $this->beginContent('@backend/views/user/admin/update.php', ['user' => $user]) ?>

<?= $this->render('trusted/_list', ['user' => $user, 'model' => $model, 'dataProvider' => $dataProvider]) ?>

<?php $this->endContent() ?>
